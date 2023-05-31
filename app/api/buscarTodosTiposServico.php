<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    $tiposServico = $tipoServicoDAO->buscarTodos('nome', 'ASC');

    if (count($tiposServico) === 0) {
        RespostaHttp::resposta('Não existem tipos de serviço cadastrados no banco de dados!', 200, []);
        exit;
    }

    foreach ($tiposServico as $tipoServico) {
        $tipoServico['id'] = intval($tipoServico['id']);
    }

    if (count($tiposServico) === 1) {
        RespostaHttp::resposta('Existe um tipo de serviço cadastrado no banco de dados!', 200, $tiposServico);
    } else {
        RespostaHttp::resposta('Existe um total de ' . count($tiposServico) . ' tipos de serviço cadastrados no banco de dados!', 200, $tiposServico);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todos os tipos de serviço!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar todos os tipos de serviço!', 200, null, false);
}