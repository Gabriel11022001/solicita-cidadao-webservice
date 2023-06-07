<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $servicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    $tiposServicoAtivos = $servicoDAO->buscarTodosTiposServicoAtivos();

    if (count($tiposServicoAtivos) === 0) {
        RespostaHttp::resposta('Não existem tipos de serviço ativos cadastrados no banco de dados!', 200, [], true);
    } else {

        foreach ($tiposServicoAtivos as $tipoServicoAtivo) {
            $tipoServicoAtivo['id'] = intval($tipoServicoAtivo['id']);
        }

        if (count($tiposServicoAtivos) === 1) {
            RespostaHttp::resposta('Existe 1 tipo de serviço ativo!', 200, $tiposServicoAtivos, true);
        } else {
            RespostaHttp::resposta('Existe um total de ' . count($tiposServicoAtivos) . ' tipos de serviço ativos!', 200, $tiposServicoAtivos, true);
        }

    }

} catch (Exception $e) {

}