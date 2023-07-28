<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');
    $equipes = $equipeDAO->buscarTodos('nome', 'ASC');

    if (count($equipes) === 0) {
        RespostaHttp::resposta('Não existem equipes cadastradas no banco de dados!', 200, [], true);
        exit;
    }
    
    foreach ($equipes as $equipe) {
        $equipe['id'] = intval($equipe['id']);
    }

    if (count($equipes) === 1) {
        RespostaHttp::resposta('Existe 1 equipe cadastrada no banco de dados!', 200, $equipes, true);
    } else {
        RespostaHttp::resposta('Existe um total de ' . count($equipes) . ' equipes cadastradas no banco de dados!', 200, $equipes, true);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todas as equipes!', $e->getMessage());
}