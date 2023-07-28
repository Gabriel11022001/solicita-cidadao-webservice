<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    /**
     * filtros aplicados: nome e cnpj
     */
    Auth::validarToken();
    
    if (!isset($_GET['filtro_texto'])) {
        RespostaHttp::resposta('O filtro de texto para pesquisa não foi informado na url, informe o mesmo para realizar a consulta!', 200, null, false);
        exit;
    }

    $filtroTexto = mb_strtoupper(trim($_GET['filtro_texto']));
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
    $instituicoes = $instituicaoDAO->buscarInstituicaoComFiltroDeTexto($filtroTexto);
    
    if (count($instituicoes) === 0) {
        RespostaHttp::resposta('Não foram encontradas instituições relacionadas ao filtro aplicado!', 200, $instituicoes);
    } else {
        RespostaHttp::resposta('Foram encontradas instituições relacionadas ao filtro aplicado!', 200, $instituicoes);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar as instituições com filtro de texto!', $e->getMessage());
}