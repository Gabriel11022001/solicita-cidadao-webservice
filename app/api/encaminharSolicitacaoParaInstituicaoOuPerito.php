<?php

use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;

try {
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

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $peritoDAO = null;
    $instituicaoDAO = null;
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');

    if ($tipoEncaminhamento === 'perito') {
        $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
        $peritoDaSolicitacao = $peritoDAO->obterIdPeritoPeloIdUsuario($peritoId);

        // validando se existe um perito cadastrado no banco de dados com o id informado
        if (!$peritoDaSolicitacao) {
            RespostaHttp::resposta('Não existe um perito cadastrado no banco de dados com esse id!', 200, null, false);
            exit;
        }

        $peritoId = intval($peritoDaSolicitacao['id']);
    } else {
        $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');

        // validando se existe uma instituição cadastrada no banco de dados com o id informado
        if (!$instituicaoDAO->buscarPeloId($instituicaoId)) {
            RespostaHttp::resposta('Não existe uma instituição cadastrada no banco de dados com esse id!', 200, null, false);
            exit;
        }

    }

    if (!$solicitacaoServicoDAO->buscarSolicitacaoPeloId($idSolicitacao)) {
        RespostaHttp::resposta('Não existe uma solicitação de serviço cadastrada no banco de dados com esse id!', 200, null, false);
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
         * enviar o e-mail para o cidadão informando o ocorrido,
         * caso a solicitação tenha sido encaminhada para a instituição,
         * enviar um e-mail para todos os gestores da instituição para informar
         * o ocorrido, caso a mesma tenha sido encaminhada a um perito,
         * enviar o e-mail para o perito.
         * */
        // registrar a notificação informando o ocorrido ao cidadão
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
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se encaminhar a solicitação!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
}