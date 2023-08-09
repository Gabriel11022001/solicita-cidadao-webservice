<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorSecretariaDAO;
use SistemaSolicitacaoServico\App\DAOS\LaudoDAO;
use SistemaSolicitacaoServico\App\DAOS\NotificacaoDAO;
use SistemaSolicitacaoServico\App\DAOS\SecretarioDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Entidades\Laudo;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\GerenciadorEmail;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    Auth::validarToken();
    $laudo = new Laudo();
    $laudo->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $laudo->setSolicitacaoPodeSerTratada(boolval(ParametroRequisicao::obterParametro('solicitacao_pode_ser_tratada')));
    $laudo->setSolicitacaoServicoId(intval(ParametroRequisicao::obterParametro('solicitacao_servico_id')));
    $laudo->setDataCadastro(new DateTime('now'));
    $errosDados = array();

    if (empty($laudo->getDescricao())) {
        $errosDados['descricao'] = 'Informe a descrição do laudo!';
    }   

    if (empty($laudo->getSolicitacaoServicoId())) {
        $errosDados['solicitacao_servico_id'] = 'Informe o id da solicitação de serviço!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    if ($laudo->getSolicitacaoServicoId() <= 0) {
        RespostaHttp::resposta('O id da solicitação de serviço não pode ser menor ou igual a 0!', 200, null, false);
        exit;
    }

    $laudoDAO = new LaudoDAO($conexaoBancoDados, 'tbl_laudos');
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    $solicitacao = $solicitacaoServicoDAO->buscarPeloId($laudo->getSolicitacaoServicoId());

    if (!$solicitacao) {
        RespostaHttp::resposta('Não existe uma solicitação cadastrada com esse id!', 200, null, false);
        exit;
    }

    if ($solicitacao['status'] === 'Aprovado pelo perito'
    || $solicitacao['status'] === 'Reprovado pelo perito') {
        RespostaHttp::resposta('Essa solicitação já foi analisada!', 200, null, false);
        exit;
    }

    $dadosLaudoCadastrar = [
        'descricao' => [
            'dado' => $laudo->getDescricao(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'solicitacao_servico_id' => [
            'dado' => $laudo->getSolicitacaoServicoId(),
            'tipo_dado' => PDO::PARAM_INT
        ],
        'data_cadastro' => [
            'dado' => $laudo->getDataCadastro()->format('Y-m-d H:i:s'),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'solicitacao_pode_ser_tratada' => [
            'dado' => $laudo->getSolicitacaoPodeSerTratada(),
            'tipo_dado' => PDO::PARAM_BOOL
        ]
    ];

    if ($laudoDAO->salvar($dadosLaudoCadastrar)) {
        $idLaudo = intval($conexaoBancoDados->lastInsertId());
        $novoStatusSolicitacao = '';
        $mensagem = 'A solicitação de servico de protocolo ' . $solicitacao['protocolo'] . ' foi ';

        if ($laudo->getSolicitacaoPodeSerTratada()) {
            $novoStatusSolicitacao = 'Aprovado pelo perito';
            $mensagem .= 'aprovada pelo perito!';
        } else {
            $novoStatusSolicitacao = 'Reprovado pelo perito';
            $mensagem = 'reprovada pelo perito!';
        }

        // alterando o status da solicitação de serviço
        if (!SolicitacaoServicoDAO::alterarStatusSolicitacao(
            $conexaoBancoDados,
            $laudo->getSolicitacaoServicoId(),
            $novoStatusSolicitacao
        )) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o laudo!', 200, null, false);
            exit;
        }

        $mensagem .= '<br>Dados da solicitação/encaminhamento:<br>
        <ul>
            <li><strong>Protocolo da solicitação:</strong> ' . $solicitacao['protocolo'] . '</li>
            <li><strong>Status da solicitação:</strong> ' . $novoStatusSolicitacao . '</li>
            <li><strong>Posição da solicitação na fila de atendimento:</strong> ' . $solicitacao['posicao_fila'] . '</li>
            <li><strong>Data de cadastro do laudo:</strong> ' . $laudo->getDataCadastro()->format('d-m-Y H:i:s') . '</li>
            <li><strong>Descrição:</strong> ' . $laudo->getDescricao() . '</li>
        </ul>';
        $notificacaoDAO = new NotificacaoDAO($conexaoBancoDados, 'tbl_notificacoes');
        $gestorSecretariaDAO = new GestorSecretariaDAO($conexaoBancoDados, 'tbl_gestores_secretaria');
        $secretarioDAO = new SecretarioDAO($conexaoBancoDados, 'tbl_secretarios');
        $emailCidadao  = CidadaoDAO::obterEmailCidadao($conexaoBancoDados, $solicitacao['cidadao_id'])['email'];
        $emailsSecretariosAtivos = $secretarioDAO->buscarEmailsSecretarios();
        $emailsGestoresSecretariaAtivos = $gestorSecretariaDAO->buscarEmailsGestoresSecretaria();
        /**
         * - enviar e-mails para os gestores de secretaria ativos, secretários ativos
         * e ao cidadão relacionado a solicitação de serviço, informando que foi registrado
         * o laudo para a solicitação e informado se a solicitação foi aprovada ou não 
         * pelo perito.
         * - registrar uma notificação para o cidadão informando se a solicitação
         * foi aprovada ou não.
         */
        $msgNotificacao = 'A solicitação de protocolo ' . $solicitacao['protocolo'] . ' foi ';

        if ($laudo->getSolicitacaoPodeSerTratada()) {
            $msgNotificacao.= 'aprovada pelo perito em ' . $laudo->getDataCadastro()->format('d-m-Y H:i:s');
        } else {
            $msgNotificacao.= 'reprovada pelo perito em ' . $laudo->getDataCadastro()->format('d-m-Y H:i:s');
        }

        $msgNotificacao.= '!';
        $dataEnvio = new DateTime('now');
        $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
        $idUsuarioNoti = $cidadaoDAO->buscarDadosCidadaoPeloIdSolicitacao($laudo->getSolicitacaoServicoId())['id'];

        // cadastrando a notificação
        if (!$notificacaoDAO->salvar([
            'mensagem' => [
                'dado' => $msgNotificacao,
                'tipo_dado' => PDO::PARAM_STR
            ],
            'solicitacao_servico_id' => [
                'dado' => $laudo->getSolicitacaoServicoId(),
                'tipo_dado' => PDO::PARAM_INT
            ],
            'usuario_id' => [
                'dado' => $idUsuarioNoti,
                'tipo_dado' => PDO::PARAM_INT
            ],
            'data_envio' => [
                'dado' => $dataEnvio->format('Y-m-d H:i:s'),
                'tipo_dado' => PDO::PARAM_STR
            ]
        ])) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o laudo!', 200, null, false);
            exit;
        }

        if (!GerenciadorEmail::enviarEmail(
            $emailCidadao,
            $mensagem,
            'Análise da solicitação de serviço'
        )) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o laudo!', 200, null, false);
            exit;
        }

        if (count($emailsSecretariosAtivos) > 0 || count($emailsGestoresSecretariaAtivos) > 0) {
            $emailsForamEnviadosComSucesso = true;

            if (count($emailsSecretariosAtivos) > 0) {

                foreach ($emailsSecretariosAtivos as $email) {
                    $emailSecretario = $email['email'];
    
                    if (!GerenciadorEmail::enviarEmail(
                        $emailSecretario,
                        $mensagem,
                        'Análise da solicitação de serviço'
                    )) {
                        $emailsForamEnviadosComSucesso = false;
                    }
    
                }

            }

            if (!$emailsForamEnviadosComSucesso) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o laudo!', 200, null, false);
                exit;
            }

            if (count($emailsGestoresSecretariaAtivos) > 0) {

                foreach ($emailsGestoresSecretariaAtivos as $email) {
                    $emailGestorSecretaria = $email['email'];

                    if (!GerenciadorEmail::enviarEmail(
                        $emailGestorSecretaria,
                        $mensagem,
                        'Análise da solicitação de serviço'
                    )) {
                        $emailsForamEnviadosComSucesso = false;
                    }

                }

            }

            if (!$emailsForamEnviadosComSucesso) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o laudo!', 200, null, false);
                exit;
            }

        }

        $conexaoBancoDados->commit();
        RespostaHttp::resposta('O Laudo foi registrado com sucesso!', 200, [
            'id' => $idLaudo,
            'descricao' => $laudo->getDescricao(),
            'data_cadastro' => $laudo->getDataCadastro()->format('d-m-Y H:i:s'),
            'id_solicitacao_servico' => $laudo->getSolicitacaoServicoId(),
            'status_solicitacao' => $novoStatusSolicitacao
        ], true);
    } else {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o laudo!', 200, null, false);
    }

} catch (AuthException $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se registrar o laudo!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o laudo!' . $e->getMessage(), 200, null, false);
}