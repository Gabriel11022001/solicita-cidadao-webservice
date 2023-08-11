<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\CodigoRecuperacaoSenhaDAO;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $novaSenha = trim(ParametroRequisicao::obterParametro('nova_senha'));
    $senhaConfirmacao = trim(ParametroRequisicao::obterParametro('senha_confirmacao'));
    $codigo = intval(ParametroRequisicao::obterParametro('codigo'));
    $errosDados = [];

    if (empty($novaSenha)) {
        $errosDados['nova_senha'] = 'Informe a nova senha!';
    }

    if (empty($senhaConfirmacao)) {
        $errosDados['senha_confirmacao'] = 'Informe a senha de confirmação!';
    }

    if (empty($codigo)) {
        $errosDados['codigo'] = 'Informe o código de confirmação!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

    if (strlen($novaSenha) < 6) {
        $errosDados['nova_senha'] = 'A senha deve possuir no mínimo 6 caracteres!';
    }

    if (strlen($senhaConfirmacao) < 6) {
        $errosDados['senha_confirmacao'] = 'A senha de confirmação deve possuir no mínimo 6 caracteres!';
    } elseif ($senhaConfirmacao != $novaSenha) {
        $errosDados['senha_confirmacao'] = 'A senha e a senha de confirmação devem ser idênticas!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, $errosDados, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $codigoRecuperacaoDAO = new CodigoRecuperacaoSenhaDAO($conexaoBancoDados, 'tbl_codigos_recuperacao_senha');
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $codigoRecuperacao = $codigoRecuperacaoDAO->buscarCodigoRecuperacaoSenha($codigo);
    
    if (!$codigoRecuperacao) {
        RespostaHttp::resposta('O código de recuperação informado não está cadastrado no banco de dados!', 200, null, false);
        exit;
    }

    $idCidadao = $codigoRecuperacao['cidadao_id'];
    $idUsuario = $cidadaoDAO->buscarPeloId($idCidadao)['usuario_id'];

    if ($usuarioDAO->alterarSenhaUsuario($idUsuario, $novaSenha)) {
        RespostaHttp::resposta('Sua senha foi alterada com sucesso!', 200, null, true);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar a senha!', 200, null, false);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se alterar a senha!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar a senha!', 200, null, false);
}