<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id não está definido como parâmetro na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);
    
    if (empty($id)) {
        RespostaHttp::resposta('Informe o id do usuário!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');
    $usuario = $usuarioDAO->buscarPeloId($id);

    if ($usuario) {
        RespostaHttp::resposta('Usuário encontrado com sucesso!', 200, $usuario, true);
    } else {
        RespostaHttp::resposta('Não existe um usuário cadastrado com esse id!', 200, null, false);
    }

} catch (Exception $e) {
    
}