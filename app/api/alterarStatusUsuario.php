<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();
    $idUsuario = trim(ParametroRequisicao::obterParametro('id'));
    $novoStatus = boolval(ParametroRequisicao::obterParametro('novo_status'));
    $errosCampos = [];

    if (empty($idUsuario)) {
        $errosCampos['id'] = 'Informe o id do usuário em questão!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosCampos, false);
        exit;
    }

    $idUsuario = intval($idUsuario);
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');

    $usuarioAlterarStatus = $usuarioDAO->buscarPeloId($idUsuario);
    
    if (!$usuarioAlterarStatus) {
        RespostaHttp::resposta('Não existe um usuário cadastrado com esse id no banco de dados!', 200, null, false);
        exit;
    }

    if ($usuarioDAO->alterarStatusUsuario($idUsuario, $novoStatus)) {
        RespostaHttp::resposta('O status do usuário foi alterado com sucesso!', 200, [
            'id' => $idUsuario,
            'novo_status' => $novoStatus
        ]);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar o status do usuário em questão!', 200, null, false);
    }
    
} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se alterar o status do usuário em questão!', $e->getMessage());
}