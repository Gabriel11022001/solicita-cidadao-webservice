<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $idUsuario = trim(ParametroRequisicao::obterParametro('id'));
    $novoStatus = boolval(ParametroRequisicao::obterParametro('novo_status'));
    $errosCampos = [];

    if (empty($idUsuario)) {
        $errosCampos['id'] = 'Informe o id do usuário em questão!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 400, $errosCampos);
        exit;
    }

    $idUsuario = intval($idUsuario);
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');

    $usuarioAlterarStatus = $usuarioDAO->buscarPeloId($idUsuario);
    
    if (!$usuarioAlterarStatus) {
        RespostaHttp::resposta('Não existe um usuário cadastrado com esse id no banco de dados!', 400, null);
        exit;
    }

    if ($usuarioDAO->alterarStatusUsuario($idUsuario, $novoStatus)) {
        RespostaHttp::resposta('O status do usuário foi alterado com sucesso!', 200, [
            'id' => $idUsuario,
            'novo_status' => $novoStatus
        ]);
    } else {
        RespostaHttp::resposta('ocorreu um erro ao tentar-se alterar o status do usuário em questão!', 400, null);
    }
    
} catch (Exception $e) {
    echo $e->getMessage();
}