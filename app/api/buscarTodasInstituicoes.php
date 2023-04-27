<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
    $instituicoes = $instituicaoDAO->buscarTodos();

    if (count($instituicoes) === 0) {
        RespostaHttp::resposta('Não existem instituições cadastradas no banco de dados!');
        exit;
    }

    foreach ($instituicoes as $instituicao) {
        $instituicao['id'] = intval($instituicao['id']);
    }

    if (count($instituicoes) === 1) {
        RespostaHttp::resposta('Existe uma instituição cadastrada no banco de dados!', 200, $instituicoes);
    } else {
        RespostaHttp::resposta('Existe um total de ' . count($instituicoes) . ' instituições cadastradas no banco de dados!', 200, $instituicoes);
    }
    
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todas as instituições cadastradas no banco de dados!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar todas as instituições cadastradas no banco de dados!', 400, null);
}