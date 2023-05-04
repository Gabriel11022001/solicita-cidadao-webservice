<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $cidadaos = $cidadaoDAO->buscarTodosCidadaos();
    
    if (count($cidadaos) === 0) {
        RespostaHttp::resposta('Não existem cidadãos cadastrados no banco de dados!', 200, []);
        exit;
    }

    if (count($cidadaos) === 1) {
        RespostaHttp::resposta('Existe um cidadão cadastrado no banco de dados', 200, $cidadaos);
        exit;
    }

    RespostaHttp::resposta('Existe um total de ' . count($cidadaos) . ' cidadãos cadastrados no banco de dados!', 200, $cidadaos);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todos os cidadãos cadastrados no banco de dados!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar todos os cidadãos cadastrados no banco de dados!', 400);
}