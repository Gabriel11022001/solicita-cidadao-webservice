<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaParametroUrl;

try {

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id não está definido como parâmetro na url!', 400, null);
        exit;
    }

    $id = intval($_GET['id']);

    if (empty($id)) {
        RespostaHttp::resposta('O id é um dado obrigatório para consulta do cidadão pelo mesmo!', 400, null);
        exit;
    }

    // validando se foi informado um id maior que 0
    if ($id < 0) {
        RespostaHttp::resposta('O id do cidadão deve ser um valor maior que 0!', 400, null);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $cidadao = $cidadaoDAO->buscarPeloId($id);
    
    if ($cidadao) {
        RespostaHttp::resposta('Cidadão encontrado com sucesso!', 200, $cidadao);
    } else {    
        RespostaHttp::resposta('Não existe um cidadão cadastrado com esse id!');
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar um cidadão pelo id!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar o cidadão pelo id!', 400, null);
}