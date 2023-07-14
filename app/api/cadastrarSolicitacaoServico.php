<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\Entidades\SolicitacaoServico;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Entidades\Endereco;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCep;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaUF;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\GeradorNumeroProtocoloSolicitacao;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
// iniciando a transação
$conexaoBancoDados->beginTransaction();

try {
    $solicitacaoServico = new SolicitacaoServico();
    $endereco = new Endereco();
    $solicitacaoServico->setTitulo(mb_strtoupper(trim(ParametroRequisicao::obterParametro('titulo'))));
    $solicitacaoServico->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $solicitacaoServico->setStatus(trim(ParametroRequisicao::obterParametro('status')));
    $dataRegistro = new DateTime('now');
    $solicitacaoServico->setDataRegistro($dataRegistro);
    $solicitacaoServico->setPrioridade(trim(ParametroRequisicao::obterParametro('prioridade')));
    $solicitacaoServico->setCidadaoId(intval(ParametroRequisicao::obterParametro('cidadao_id')));
    $solicitacaoServico->setUrlFoto(trim(ParametroRequisicao::obterParametro('url_foto')));
    $endereco->setLogradouro(trim(ParametroRequisicao::obterParametro('logradouro')));
    $endereco->setComplemento(trim(ParametroRequisicao::obterParametro('complemento')));
    $endereco->setCep(trim(ParametroRequisicao::obterParametro('cep')));
    $endereco->setNumero(trim(ParametroRequisicao::obterParametro('numero')));
    $endereco->setBairro(trim(ParametroRequisicao::obterParametro('bairro')));
    $endereco->setCidade(trim(ParametroRequisicao::obterParametro('cidade')));
    $endereco->setEstado(trim(ParametroRequisicao::obterParametro('estado')));
    $solicitacaoServico->setEndereco($endereco);
    $errosDados = ValidaCamposObrigatorios::validarFormularioCadastrarSolicitacaoServico($solicitacaoServico);

    // validando se os dados obrigatórios foram informados
    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    if (mb_strlen($solicitacaoServico->getPrioridade()) > 255) {
        $errosDados['prioridade'] = 'A prioridade não deve possuir mais que 255 caracteres!';
    } elseif ($solicitacaoServico->getPrioridade() != 'Alta'
    && $solicitacaoServico->getPrioridade() != 'Normal'
    && $solicitacaoServico->getPrioridade() != 'Baixa') {
        $errosDados['prioridade'] = 'A prioridade informada é inválida!';
    }

    if (!ValidaCep::validarCep($endereco->getCep())) {
        $errosDados['cep'] = 'Cep inválido!';
    }

    if (!ValidaUF::validarUF($endereco->getEstado())) {
        $errosDados['estado'] = 'Estado inválido!';
    }

    if (mb_strlen($solicitacaoServico->getTitulo()) < 6) {
        $errosDados['titulo'] = 'O título deve possuir no mínimo 6 caracteres!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, $errosDados, false);
        exit;
    }

    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');

    // validando se existe um cidadão cadastrado com o id informado
    if (!$cidadaoDAO->buscarPeloId($solicitacaoServico->getCidadaoId())) {
        RespostaHttp::resposta('Não existe um cidadão cadastrado com esse id no banco de dados!', 200, null, false);
        exit;
    }

    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    // definindo a posição da solicitação na fila
    $posicaoFilaSolicitacao = 0;
    $solicitacoesComPrioridadeAlta = $solicitacaoServicoDAO->buscarTodasSolicitacoesComPrioridadeAlta();
    $solicitacoesComPrioridadeNormal = $solicitacaoServicoDAO->buscarTodasSolicitacoesComPrioridadeNormal();
    $solicitacoesComPrioridadeBaixa = $solicitacaoServicoDAO->buscarTodasSolicitacoesComPrioridadeBaixa();

    if (count($solicitacoesComPrioridadeAlta) === 0
    && count($solicitacoesComPrioridadeBaixa) === 0
    && count($solicitacoesComPrioridadeNormal) === 0) {
        $posicaoFilaSolicitacao = 1;
    } else {

        if ($solicitacaoServico->getPrioridade() === 'Alta') {

            if (count($solicitacoesComPrioridadeAlta) > 0) {
                $posicaoFilaSolicitacao = $solicitacoesComPrioridadeAlta[count($solicitacoesComPrioridadeAlta) - 1]['posicao_fila'] + 1;
            } else {
                $posicaoFilaSolicitacao = 1;
            }

            if (count($solicitacoesComPrioridadeNormal) > 0) {
                // atualizando a posição de todas as solicitações com prioridade normal

                foreach ($solicitacoesComPrioridadeNormal as $solicitacaoComPrioridadeNormal) {
                    $novaPosicaoFila = $solicitacaoComPrioridadeNormal['posicao_fila'] + 1;

                    if (!$solicitacaoServicoDAO->alterarPosicaoSolicitacaoNaFilaDeAtendimento(
                        $solicitacaoComPrioridadeNormal['id'],
                        $novaPosicaoFila
                    )) {
                        $conexaoBancoDados->rollBack();
                        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a solicitação de serviço!', 200, null, false);
                        exit;
                    }

                }

            }

            if (count($solicitacoesComPrioridadeBaixa) > 0) {
                // alterando a posição na fila das solicitações com prioridade baixa

                foreach ($solicitacoesComPrioridadeBaixa as $solicitacaoComPrioridadeBaixa) {
                    $novaPosicaoFila = $solicitacaoComPrioridadeBaixa['posicao_fila'] + 1;

                    if (!$solicitacaoServicoDAO->alterarPosicaoSolicitacaoNaFilaDeAtendimento(
                        $solicitacaoComPrioridadeBaixa['id'],
                        $novaPosicaoFila
                    )) {
                        $conexaoBancoDados->rollBack();
                        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a solicitação de serviço!', 200, null, false);
                        exit;
                    }

                }

            }

        } elseif ($solicitacaoServico->getPrioridade() === 'Normal') {

            if (count($solicitacoesComPrioridadeNormal) > 0) {
                $posicaoFilaSolicitacao = $solicitacoesComPrioridadeNormal[count($solicitacoesComPrioridadeNormal) - 1]['posicao_fila'] + 1;
            } else {

                if (count($solicitacoesComPrioridadeAlta) > 0) {
                    $posicaoFilaSolicitacao = $solicitacoesComPrioridadeAlta[count($solicitacoesComPrioridadeAlta) - 1]['posicao_fila'] + 1;
                } else if (count($solicitacoesComPrioridadeBaixa) > 0) {
                    $posicaoFilaSolicitacao = $solicitacoesComPrioridadeBaixa[0]['posicao_fila'];
                }

            }

            if (count($solicitacoesComPrioridadeBaixa) > 0) {
                // alterando a posição na fila das solicitações com prioridade baixa

                foreach ($solicitacoesComPrioridadeBaixa as $solicitacaoComPrioridadeBaixa) {
                    $novaPosicaoFila = $solicitacaoComPrioridadeBaixa['posicao_fila'] + 1;

                    if (!$solicitacaoServicoDAO->alterarPosicaoSolicitacaoNaFilaDeAtendimento(
                        $solicitacaoComPrioridadeBaixa['id'],
                        $novaPosicaoFila
                    )) {
                        $conexaoBancoDados->rollBack();
                        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a solicitação de serviço!', 200, null, false);
                        exit;
                    }

                }

            }

        } else {

            if (count($solicitacoesComPrioridadeBaixa) > 0) {
                $posicaoFilaSolicitacao = $solicitacoesComPrioridadeBaixa[count($solicitacoesComPrioridadeBaixa) - 1]['posicao_fila'] + 1;
            } elseif (count($solicitacoesComPrioridadeNormal) > 0) {
                $posicaoFilaSolicitacao = $solicitacoesComPrioridadeNormal[count($solicitacoesComPrioridadeNormal) - 1]['posicao_fila'] + 1;
            } else {
                $posicaoFilaSolicitacao = $solicitacoesComPrioridadeAlta[count($solicitacoesComPrioridadeAlta) - 1]['posicao_fila'] + 1;
            }

        }

    }

    $solicitacaoServico->setPosicaoFilaAtendimento($posicaoFilaSolicitacao);
    $dadosSolicitacaoCadastrar = [
        'titulo' => [ 'dado' => $solicitacaoServico->getTitulo(), 'tipo_dado' => PDO::PARAM_STR ],
        'descricao' => [ 'dado' => $solicitacaoServico->getDescricao(), 'tipo_dado' => PDO::PARAM_STR ],
        'status' => [ 'dado' => $solicitacaoServico->getStatus(), 'tipo_dado' => PDO::PARAM_STR ],
        'data_registro' => [ 'dado' => $solicitacaoServico->getDataRegistro()->format('Y-m-d H:i:s'), 'tipo_dado' => PDO::PARAM_STR ],
        'cidadao_id' => [ 'dado' => $solicitacaoServico->getCidadaoId(), 'tipo_dado' => PDO::PARAM_INT ],
        'url_foto' => [ 'dado' => $solicitacaoServico->getUrlFoto(), 'tipo_dado' => PDO::PARAM_STR ],
        'posicao_fila' => [ 'dado' => $solicitacaoServico->getPosicaoFilaAtendimento(), 'tipo_dado' => PDO::PARAM_INT ],
        'logradouro' => [
            'dado' => $solicitacaoServico->getEndereco()->getLogradouro(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'complemento' => [
            'dado' => $solicitacaoServico->getEndereco()->getComplemento(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cep' => [
            'dado' => $solicitacaoServico->getEndereco()->getCep(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'bairro' => [
            'dado' => $solicitacaoServico->getEndereco()->getBairro(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cidade' => [
            'dado' => $solicitacaoServico->getEndereco()->getCidade(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'estado' => [
            'dado' => $solicitacaoServico->getEndereco()->getEstado(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'numero' => [
            'dado' => $solicitacaoServico->getEndereco()->getNumero(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'prioridade' => [ 'dado' => $solicitacaoServico->getPrioridade(), 'tipo_dado' => PDO::PARAM_STR ]
    ];

    if ($solicitacaoServicoDAO->salvar($dadosSolicitacaoCadastrar)) {
        $idSolicitacaoServico = $conexaoBancoDados->lastInsertId();
        $solicitacaoServico->setNumeroProtocolo(
            GeradorNumeroProtocoloSolicitacao::gerarNumeroProtocolo($idSolicitacaoServico)
        );
        // atualizando o número de protocolo da solicitação de serviço

        if ($solicitacaoServicoDAO->alterarNumeroProtocoloSolicitacaoServico($idSolicitacaoServico, $solicitacaoServico->getNumeroProtocolo())) {
            $conexaoBancoDados->commit();
            RespostaHttp::resposta('A solicitação de serviço foi cadastrada com sucesso!', 201, [
                'id' => $idSolicitacaoServico,
                'numero_protocolo' => $solicitacaoServico->getNumeroProtocolo(),
                'titulo' => $solicitacaoServico->getTitulo(),
                'descricao' => $solicitacaoServico->getDescricao(),
                'data_registro' => $solicitacaoServico->getDataRegistro()->format('d-m-Y H:i:s'),
                'status' => $solicitacaoServico->getStatus(),
                'prioridade' => $solicitacaoServico->getPrioridade(),
                'posicao_fila' => $solicitacaoServico->getPosicaoFilaAtendimento(),
                'cidadao_id' => $solicitacaoServico->getCidadaoId()
            ]);
        } else {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a solicitação de serviço!', 200, null, false);
        }

    } else {
        // realizando o rollback
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a solicitação de serviço!', 200, null, false);
    }

} catch (Exception $e) {
    // realizando o rollback da transação
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar a solicitação de serviço!', $e->getMessage());
}