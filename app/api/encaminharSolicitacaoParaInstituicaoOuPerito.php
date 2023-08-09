<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\NotificacaoDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\GerenciadorEmail;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    Auth::validarToken();
    $instituicaoId = null;
    $peritoId = null;
    $novoStatusSolicitacao = '';
    $observacaoGestorSecretaria = null;
    $observacaoSecretario = null;
    $tipoEncaminhamento = trim(ParametroRequisicao::obterParametro('tipo_encaminhamento'));
    $nivelAcessoUsuarioRealizandoSolicitacao = trim(ParametroRequisicao::obterParametro('nivel_acesso_usuario_realizando_solicitacao'));
    $idSolicitacao = intval(ParametroRequisicao::obterParametro('id_solicitacao'));
    $prioridade = trim(ParametroRequisicao::obterParametro('prioridade'));
    $errosDados = array();

    if (empty($tipoEncaminhamento)) {
        RespostaHttp::resposta('Informe o tipo de encaminhamento da solicitação!', 200, null, false);
        exit;
    }

    if ($tipoEncaminhamento != 'perito' && $tipoEncaminhamento != 'instituicao') {
        RespostaHttp::resposta('Tipo de encaminhamento inválido!', 200, null, false);
        exit;
    }

    if ($tipoEncaminhamento === 'perito') {
        $peritoId = intval(ParametroRequisicao::obterParametro('id_perito'));
    } else {
        $instituicaoId = intval(ParametroRequisicao::obterParametro('id_instituicao'));
    }

    if ($tipoEncaminhamento === 'perito') {
        $novoStatusSolicitacao = 'Aguardando análise do perito';
    } else {
        $novoStatusSolicitacao = 'Aguardando encaminhamento a equipe responsável';
    }

    if ($nivelAcessoUsuarioRealizandoSolicitacao === 'secretario') {
        $observacaoSecretario = trim(ParametroRequisicao::obterParametro('observacao_secretario'));
    } elseif($nivelAcessoUsuarioRealizandoSolicitacao === 'gestor-secretaria') {
        $observacaoGestorSecretaria = trim(ParametroRequisicao::obterParametro('observacao_gestor_secretaria'));
    } else {
        RespostaHttp::resposta('Informe corretamente o tipo de usuário que está fazendo a requisição(secretario ou gestor-secretaria)!', 200, null, false);
        exit;
    }

    if (empty($idSolicitacao)) {
        $errosDados['id_solicitacao'] = 'Informe o id da solicitação!';
    }

    if ($tipoEncaminhamento === 'perito' && empty($peritoId)) {
        $errosDados['id_perito'] = 'Informe o id do perito!';
    } elseif ($tipoEncaminhamento === 'instituicao' && empty($instituicaoId)) {
        $errosDados['id_instituicao'] = 'Informe o id da instituição!';
    }

    if (empty($prioridade)) {
        $errosDados['prioridade'] = 'Informe a prioridade!';
    } elseif ($prioridade != 'Alta' && $prioridade != 'Baixa' && $prioridade != 'Normal') {
        $errosDados['prioridade'] = 'Prioridade inválida!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Dados inválidos!', 200, $errosDados, false);
        exit;
    }

    $peritoDAO = null;
    $instituicaoDAO = null;
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');

    if ($tipoEncaminhamento === 'perito') {
        $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
        $peritoDaSolicitacao = $peritoDAO->buscarPeloId($peritoId);

        // validando se existe um perito cadastrado no banco de dados com o id informado
        if (!$peritoDaSolicitacao) {
            RespostaHttp::resposta('Não existe um perito cadastrado no banco de dados com esse id!', 200, null, false);
            exit;
        }

        // validando se o perito está ativo
        if (!$peritoDaSolicitacao['status']) {
            RespostaHttp::resposta('O perito em questão está com o perfil inativo!', 200, null, false);
            exit;
        }

    } else {
        $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
        $instituicaoDaSolicitacao = $instituicaoDAO->buscarPeloId($instituicaoId);

        // validando se existe uma instituição cadastrada no banco de dados com o id informado
        if (!$instituicaoDaSolicitacao) {
            RespostaHttp::resposta('Não existe uma instituição cadastrada no banco de dados com esse id!', 200, null, false);
            exit;
        }

        // validando se a instituição está ativa
        if (!$instituicaoDaSolicitacao) {
            RespostaHttp::resposta('A instituição em questão não está ativa!', 200, null, false);
            exit;
        }

    }

    $solicitacao = $solicitacaoServicoDAO->buscarPeloId($idSolicitacao);

    if (!$solicitacao) {
        RespostaHttp::resposta('Não existe uma solicitação de serviço cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    // validando se a solicitação já foi encaminhada
    if (!empty($solicitacao['perito_id'])
    || !empty($solicitacao['instituicao_id'])
    || !empty($solicitacao['equipe_id'])) {
        RespostaHttp::resposta('Essa solicitação já foi encaminhada!', 200, null, false);
        exit;
    }

    if ($solicitacaoServicoDAO->encaminharSolicitacaoParaInstituicaoOuPerito(
        $idSolicitacao,
        $instituicaoId,
        $peritoId,
        $novoStatusSolicitacao,
        $prioridade,
        $observacaoSecretario,
        $observacaoGestorSecretaria
    )) {
        /*
         * - enviar o e-mail para o cidadão informando o ocorrido,
         * caso a solicitação tenha sido encaminhada para a instituição,
         * enviar um e-mail para todos os gestores da instituição para informar
         * o ocorrido, caso a mesma tenha sido encaminhada a um perito,
         * enviar o e-mail para o perito.
         * - cadastrar uma notificação para o cidadão informando
         * que a solicitação de serviço dele foi encaminhada para um perito
         * ou para uma instituição.
         * */
        $emailsForamEnviadosComSucesso = true;
        $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
        $gestorInstituicaoDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
        $emailsGestoresInstituicao = [];
        $emailPerito = '';
        $emailCidadao = '';
        $dadosCidadaoRelacionadoASolicitacao = $cidadaoDAO->buscarDadosCidadaoPeloIdSolicitacao($idSolicitacao);
        $emailCidadao = $dadosCidadaoRelacionadoASolicitacao['email'];
        $nomeCidadao = $dadosCidadaoRelacionadoASolicitacao['nome'];
        $cpfCidadao = $dadosCidadaoRelacionadoASolicitacao['cpf'];
        $mensagemCidadao = '';
        $solicitacaoEncaminhamento = $solicitacaoServicoDAO->buscarPeloId($idSolicitacao);
        $dataEncaminhamento = new DateTime('now');
        $dataEncaminhamento = $dataEncaminhamento->format('d-m-Y H:i:s');
        $notificacaoDAO = new NotificacaoDAO($conexaoBancoDados, 'tbl_notificacoes');
        $dataRegistroNotificacao = new DateTime('now');
        $dataRegistroNotificacao = $dataRegistroNotificacao->format('Y-m-d H:i:s');
        $dadosNotificacaoCadastrar = [
            'mensagem' => '',
            'usuario_id' => $dadosCidadaoRelacionadoASolicitacao['id'],
            'solicitacao_servico_id' => $idSolicitacao,
            'data_envio' => $dataRegistroNotificacao
        ];
        $observacaoMensagemEmail = '';

        if ($nivelAcessoUsuarioRealizandoSolicitacao === 'secretario') {
            $observacaoMensagemEmail = $observacaoSecretario;
        } else {
            $observacaoMensagemEmail = $observacaoGestorSecretaria;
        }

        if (empty($observacaoMensagemEmail)) {
            $observacaoMensagemEmail = '----- Sem observação -----';
        }

        if ($tipoEncaminhamento === 'perito') {
            $dadosNotificacaoCadastrar['mensagem'] = 'Sua solicitação foi encaminhada a um perito para análise!';
            $mensagemCidadao = 'Sua solicitação foi encaminhada a um perito para análise<br>
            Dados da solicitação:
            <ul>
                <li><strong>Protocolo da solicitação:</strong> ' . $solicitacaoEncaminhamento['protocolo'] . '</li>
                <li><strong>Data de encaminhamento:</strong> ' . $dataEncaminhamento . '</li>
                <li><strong>Posição da solicitação na fila de atendimento:</strong> ' . $solicitacaoEncaminhamento['posicao_fila'] . '</li>
            </ul>';
        } else {
            $dadosNotificacaoCadastrar['mensagem'] = 'Sua solicitação foi encaminhada a uma instituição para tratamento!';
            $mensagemCidadao = 'Sua solicitação foi encaminhada a uma instituição para tratamento<br>
            Dados da solicitação:
            <ul>
                <li><strong>Protocolo da solicitação:</strong> ' . $solicitacaoEncaminhamento['protocolo'] . '</li>
                <li><strong>Data de encaminhamento:</strong> ' . $dataEncaminhamento . '</li>
                <li><strong>Posição da solicitação na fila de atendimento:</strong> ' . $solicitacaoEncaminhamento['posicao_fila'] . '</li>
            </ul>';
        }

        // cadastrando a notificação para o cidadão
        if (!$notificacaoDAO->salvar([
            'mensagem' => [
                'dado' => $dadosNotificacaoCadastrar['mensagem'],
                'tipo_dado' => PDO::PARAM_STR
            ],
            'usuario_id' => [
                'dado' => $dadosNotificacaoCadastrar['usuario_id'],
                'tipo_dado' => PDO::PARAM_INT
            ],
            'solicitacao_servico_id' => [
                'dado' => $dadosNotificacaoCadastrar['solicitacao_servico_id'],
                'tipo_dado' => PDO::PARAM_INT
            ],
            'data_envio' => [
                'dado' => $dadosNotificacaoCadastrar['data_envio'],
                'tipo_dado' => PDO::PARAM_STR
            ]
        ])) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
            exit;
        }

        // encaminhando o e-mail para o cidadão
        if (!GerenciadorEmail::enviarEmail(
            $emailCidadao,
            $mensagemCidadao,
            'Encaminhamento de solicitação de serviço'
        )) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
            exit;
        }

        if ($tipoEncaminhamento === 'perito') {
            // enviar e-mail para o perito
            $emailPerito = $peritoDAO->buscarEmailPeritoPeloIdPerito($peritoId)['email'];
            $mensagemPerito = 'Foi encaminhado a você uma solicitação para análise<br>
            Dados da solicitação:<br>
            <ul>
                <li><strong>Cidadão:</strong> ' . $nomeCidadao . '</li>
                <li><strong>Cpf do cidadão:</strong> ' . $cpfCidadao . '</li>
                <li><strong>Protocolo:</strong> ' . $solicitacaoEncaminhamento['protocolo'] . '</li>
                <li><strong>Status:</strong> ' . $novoStatusSolicitacao . '</li>
                <li><strong>Data de encaminhamento:</strong> ' . $dataEncaminhamento . '</li>
                <li><strong>Observação:</strong> ' . $observacaoMensagemEmail . '</li>
                <li><strong>Logradouro:</strong> ' . $solicitacaoEncaminhamento['logradouro'] . '</li>
                <li><strong>Complemento:</strong> ' . $solicitacaoEncaminhamento['complemento'] . '</li>
                <li><strong>Bairro:</strong> ' . $solicitacaoEncaminhamento['bairro'] . '</li>
                <li><strong>Cidade:</strong> ' . $solicitacaoEncaminhamento['cidade'] . '</li>
                <li><strong>Estado:</strong> ' . $solicitacaoEncaminhamento['estado'] . '</li>
                <li><strong>Número de residência:</strong> ' . $solicitacaoEncaminhamento['numero'] . '</li>
                <li><strong>CEP:</strong> ' . $solicitacaoEncaminhamento['cep'] . '</li>
                <li><strong>Prioridade da solicitação:</strong> ' . $solicitacaoEncaminhamento['prioridade'] . '</li>
            </ul>';
            
            if (!GerenciadorEmail::enviarEmail($emailPerito, $mensagemPerito, 'Encaminhamento de solicitação de serviço')) {
                $emailsForamEnviadosComSucesso = false;
            }

        } else {
            // enviar e-mails para os gestores de instituição
            $emailsGestoresInstituicao = $gestorInstituicaoDAO->buscarEmailsGestoresInstituicao($instituicaoId);

            if (count($emailsGestoresInstituicao) > 0) {
                $mensagemGestoresInstituicao = 'Foi encaminhado a instituição uma solicitação para tratamento<br>
                Dados da solicitação:<br>
                <ul>
                    <li><strong>Cidadão:</strong> ' . $nomeCidadao . '</li>
                    <li><strong>Cpf do cidadão:</strong> ' . $cpfCidadao . '</li>
                    <li><strong>Protocolo:</strong> ' . $solicitacaoEncaminhamento['protocolo'] . '</li>
                    <li><strong>Status:</strong> ' . $novoStatusSolicitacao . '</li>
                    <li><strong>Data de encaminhamento:</strong> ' . $dataEncaminhamento . '</li>
                    <li><strong>Observação:</strong> ' . $observacaoMensagemEmail . '</li>
                    <li><strong>Logradouro:</strong> ' . $solicitacaoEncaminhamento['logradouro'] . '</li>
                    <li><strong>Complemento:</strong> ' . $solicitacaoEncaminhamento['complemento'] . '</li>
                    <li><strong>Bairro:</strong> ' . $solicitacaoEncaminhamento['bairro'] . '</li>
                    <li><strong>Cidade:</strong> ' . $solicitacaoEncaminhamento['cidade'] . '</li>
                    <li><strong>Estado:</strong> ' . $solicitacaoEncaminhamento['estado'] . '</li>
                    <li><strong>Número de residência:</strong> ' . $solicitacaoEncaminhamento['numero'] . '</li>
                    <li><strong>CEP:</strong> ' . $solicitacaoEncaminhamento['cep'] . '</li>
                    <li><strong>Prioridade da solicitação:</strong> ' . $solicitacaoEncaminhamento['prioridade'] . '</li>
                </ul>';

                foreach ($emailsGestoresInstituicao as $email) {
                    $emailGestorInstituicao = $email['email'];
                    
                    if (!GerenciadorEmail::enviarEmail(
                        $emailGestorInstituicao,
                        $mensagemGestoresInstituicao,
                        'Encaminhamento de solicitação de serviço'
                    )) {
                        $emailsForamEnviadosComSucesso = false;
                        break;
                    }

                }

            }

        }

        if ($emailsForamEnviadosComSucesso) {
            $conexaoBancoDados->commit();
            RespostaHttp::resposta('Solicitação encaminhada com sucesso!', 200, [
                'id' => $idSolicitacao,
                'status' => $novoStatusSolicitacao,
                'prioridade' => $prioridade,
                'id_perito' => $peritoId,
                'id_instituicao' => $instituicaoId,
                'obs_secretario' => $observacaoSecretario,
                'obs_gestor_secretaria' => $observacaoGestorSecretaria
            ], true);
        } else {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
        }

    } else {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
    }

} catch (AuthException $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se encaminhar a solicitação!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação: ' . $e->getMessage(), 200, null, false);
}