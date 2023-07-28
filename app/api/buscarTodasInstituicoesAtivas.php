<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
    $instituicoes = $instituicaoDAO->buscarTodasInstituicoesAtivas();
    
    if (count($instituicoes) === 0) {
        RespostaHttp::resposta('Não existem instituições ativas cadastradas no banco de dados!');
    } else {

        foreach ($instituicoes as $instituicao) {
            $instituicao['id'] = intval($instituicao['id']);
        }

        if (count($instituicoes) === 1) {
            RespostaHttp::resposta('Existe 1 instituição ativa cadastrada no banco de dados!', 200, $instituicoes, true);
        } else {
            RespostaHttp::resposta('Existe um total de ' . count($instituicoes) . ' instituições ativas cadastradas no banco de dados!', 200, $instituicoes, true);
        }
        
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todas as instituições ativas!', $e->getMessage());
}