<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $id = intval(ParametroRequisicao::obterParametro('id'));
    $senhaAntiga = trim(ParametroRequisicao::obterParametro('senha_antiga'));
    $novaSenha = trim(ParametroRequisicao::obterParametro('nova_senha'));
    $senhaConfirmacao = trim(ParametroRequisicao::obterParametro('senha_confirmacao'));
    $errosDados = [];

    if (empty($id)) {
        $errosDados['id'] = 'Informe o id do usuário!';
    }

    if (empty($senhaAntiga)) {
        $errosDados['senha_antiga'] = 'Informe a senha antiga!';
    }

    if (empty($novaSenha)) {
        $errosDados['nova_senha'] = 'Informe a nova senha!';
    }

    if (empty($senhaConfirmacao)) {
        $errosDados['senha_confirmacao'] = 'Informe a senha de confirmação!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, null, false);
        exit;
    }

    if (mb_strlen($novaSenha) < 6 || mb_strlen($novaSenha) > 25) {
        $errosDados['nova_senha'] = 'A senha deve possuir no mínimo 6 caracteres e no máximo 25 caracteres!';
    }

    if (mb_strlen($senhaConfirmacao) < 6 || mb_strlen($senhaConfirmacao) > 25) {
        $errosDados['senha_confirmacao'] = 'A senha de confirmação deve possuir no mínimo 6 caracteres e no máximo 25 caracteres!';
    } elseif ($senhaConfirmacao != $novaSenha) {
        $errosDados['senha_confirmacao'] = 'A senha de confirmação deve ser igual a nova senha!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, null, false);
        exit;
    }

    $novaSenha = md5($novaSenha);
    $senhaAntiga = md5($senhaAntiga);
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');

    if (!$usuarioDAO->buscarUsuarioPeloIdESenha($id, $senhaAntiga)) {
        RespostaHttp::resposta('Não foi encontrado um usuário cadastrado com esse id e senha no banco de dados!', 200, null, false);
        exit;
    }
    
    if ($usuarioDAO->alterarSenhaUsuario($id, $novaSenha)) {
        // enviar e-mail informando que a senha foi alterada
        
        RespostaHttp::resposta('Senha alterada com sucesso!');
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar a senha do perfil em questão!', 200, null, false);
    }

} catch (Exception $e) {

}