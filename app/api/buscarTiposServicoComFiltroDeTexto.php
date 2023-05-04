<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $filtroTexto = strtoupper(ParametroRequisicao::obterParametro('filtro_texto'));
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    $tiposServicosEncontradosComFiltragem = $tipoServicoDAO->buscarTiposServicoComFiltro($filtroTexto);

    if (count($tiposServicosEncontradosComFiltragem) === 0) {
        RespostaHttp::resposta('Não foram encontrados tipos de serviços relacionados ao filtro de texto aplicado!', 200, []);
    } else {
        RespostaHttp::resposta('Foram encontrados tipos de serviços relacionados ao filtro aplicado!', 200, $tiposServicosEncontradosComFiltragem);
    }
    
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar os tipos de serviço com filtro de texto!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar os tipos de serviço com filtro de texto!', 200, null, false);
}