<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    /**
     * filtros aplicados: nome e cnpj
     */
    
    if (!isset($_GET['filtro_texto'])) {
        RespostaHttp::resposta('O filtro de texto para pesquisa não foi informado na url, informe o mesmo para realizar a consulta!', 200, null, false);
        exit;
    }

    $filtroTexto = mb_strtoupper(trim($_GET['filtro_texto']));
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
    $instituicoes = $instituicaoDAO->buscarInstituicaoComFiltroDeTexto($filtroTexto);
    
    if (count($instituicoes) === 0) {
        RespostaHttp::resposta('Não foram encontradas instituições relacionadas ao filtro aplicado!', 200, []);
    } else {
        RespostaHttp::resposta('Foram encontradas instituições relacionadas ao filtro aplicado!', 200, $instituicoes);
    }

} catch (Exception $e) {
    echo $e->getMessage();
}