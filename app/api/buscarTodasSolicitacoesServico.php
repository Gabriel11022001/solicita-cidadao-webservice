<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    $solicitacoesServico = $solicitacaoServicoDAO->buscarTodasSolicitacoesServico();

    if (count($solicitacoesServico) === 0) {
        RespostaHttp::resposta('Não existem solicitações de serviço cadastradas no banco de dados!');
    } else {

        foreach ($solicitacoesServico as $solicitacaoServico) {
            $solicitacaoServico['id'] = intval($solicitacaoServico['id']);
        }

        if (count($solicitacoesServico) === 1) {
            RespostaHttp::resposta('Existe 1 solicitação de serviço cadastrada no banco de dados!', 200, $solicitacoesServico, true);
        } else {
            RespostaHttp::resposta('Existe um total de ' . count($solicitacoesServico) . ' solicitações de serviço cadastradas no banco de dados!', 200, $solicitacoesServico, true);
        }

    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todas as solicitações de serviço!', $e->getMessage());
}