<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    /**
     * filtros aplicados: nome e cnpj
     */
    $filtroTexto = mb_strtoupper(ParametroRequisicao::obterParametro('filtro_texto'));
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