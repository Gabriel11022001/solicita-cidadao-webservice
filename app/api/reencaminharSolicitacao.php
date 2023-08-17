<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    $solicitacaoId = intval(ParametroRequisicao::obterParametro('solicitacao_id'));
    $tipoReenchaminhamento = trim(ParametroRequisicao::obterParametro('tipo_reencaminhamento'));
    $idPerito = null;
    $idInstituicao = null;
    $tipoUsuarioEstaRealizandoProcesso = trim(ParametroRequisicao::obterParametro('tipo_usuario'));
    $observacaoGestorSecretaria = '';
    $observacaoSecretario = '';
    $errosDados = [];

    if (empty($solicitacaoId)) {
        $errosDados['solicitacao_id'] = 'Informe o id da solicitação de serviço!';
    }

    if (empty($tipoReenchaminhamento)) {
        $errosDados['tipo_reencaminhamento'] = 'Informe o tipo de reencaminhamento que você deseja realizar!';
    }

    if (empty($tipoUsuarioEstaRealizandoProcesso)) {
        $errosDados['tipo_usuario'] = 'Informe qual o tipo do usuário que está realizando o processo!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

} catch (AuthException $e) {

} catch (Exception $e) {

}