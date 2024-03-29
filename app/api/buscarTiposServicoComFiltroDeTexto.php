<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    Auth::validarToken();

    if (!isset($_GET['filtro_texto'])) {
        RespostaHttp::resposta('O filtro de texto para pesquisa não foi informado na url, informe o mesmo para realizar a consulta!', 200, null, false);
        exit;
    }

    $filtroTexto = mb_strtoupper(trim($_GET['filtro_texto']));
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    $tiposServicosEncontradosComFiltragem = $tipoServicoDAO->buscarTiposServicoComFiltro($filtroTexto);

    if (count($tiposServicosEncontradosComFiltragem) === 0) {
        RespostaHttp::resposta('Não foram encontrados tipos de serviços relacionados ao filtro de texto aplicado!', 200, []);
    } else {
        RespostaHttp::resposta('Foram encontrados tipos de serviços relacionados ao filtro aplicado!', 200, $tiposServicosEncontradosComFiltragem);
    }
    
} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar os tipos de serviço com filtro de texto!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar os tipos de serviço com filtro de texto!', 200, null, false);
}