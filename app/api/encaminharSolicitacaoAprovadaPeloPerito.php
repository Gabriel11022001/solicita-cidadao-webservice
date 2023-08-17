<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\GerenciadorEmail;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    Auth::validarToken();
    $solicitacaoId = intval(ParametroRequisicao::obterParametro('solicitacao_id'));
    $instituicaoId = intval(ParametroRequisicao::obterParametro('instituicao_id'));
    $novoStatusSolicitacao = 'Aguardando encaminhamento a equipe responsável';
    $errosDados = [];

    if (empty($solicitacaoId)) {
        $errosDados['solicitacao_id'] = 'Informe o id da solicitação!';
    }

    if (empty($instituicaoId)) {
        $errosDados['instituicao_id'] = 'Informe o id da instituição!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
    $solicitacao = $solicitacaoServicoDAO->buscarSolicitacaoPeloId($solicitacaoId);
    $instituicao = $instituicaoDAO->buscarPeloId($instituicaoId);

    if (!$solicitacao) {
        RespostaHttp::resposta('Não existe uma solicitação cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    if (!$instituicao) {
        RespostaHttp::resposta('Não existe uma instituição cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    if ($solicitacao['status'] != 'Aprovado pelo perito') {
        RespostaHttp::resposta('Essa solicitação não pode ser encaminhada!', 200, null, false);
        exit;
    }

    if (!$instituicao['status']) {
        RespostaHttp::resposta('Essa instituição não está ativa!', 200, null, false);
        exit;
    }

    if (!empty($solicitacao['instituicao_id'])) {
        RespostaHttp::resposta('Essa solicitação já foi encaminhada para uma instituição, você não pode encaminhar ela novamente!', 200, null, false);
        exit;
    }

    // encaminhando a solicitação para uma instituição
    if (!SolicitacaoServicoDAO::encaminharSolicitacaoParaInstituicao(
        $conexaoBancoDados,
        $solicitacaoId,
        $instituicaoId,
        $novoStatusSolicitacao
    )) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
        exit;
    }

    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $gestorInstituicaoDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
    $emailCidadao = $cidadaoDAO->buscarPeloId($solicitacao['cidadao_id'])['email'];
    $emailsGestoresInstituicao = $gestorInstituicaoDAO->buscarEmailsGestoresInstituicao($instituicaoId);

    if (!GerenciadorEmail::enviarEmail(
        $emailCidadao,
        '',
        'Encaminhamento de solicitação'
    )) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
        exit;
    }

    $todosEmailsForamEnviados = true;

    foreach ($emailsGestoresInstituicao as $email) {
        $emailGestor = $email['email'];

        if (!GerenciadorEmail::enviarEmail(
            $emailGestor,
            '',
            'Encaminhamento de solicitação'
        )) {
            $todosEmailsForamEnviados = false;
        }

    }

    if (!$todosEmailsForamEnviados) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se encaminhar a solicitação!', 200, null, false);
        exit;
    }

    $conexaoBancoDados->commit();
    RespostaHttp::resposta('A solicitação foi encaminhada com sucesso para a instituição!', 200, [
        'id_solicitacao' => $solicitacaoId,
        'id_instituicao' => $instituicaoId,
        'novo_status_solicitacao' => $novoStatusSolicitacao
    ], true);
} catch (AuthException $e) {

} catch (Exception $e) {

}