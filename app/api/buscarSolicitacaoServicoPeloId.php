<?php

use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id deve ser informado na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);

    if (empty($id)) {
        RespostaHttp::resposta('Informe o id da solicitação de serviço!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    $solicitacao = $solicitacaoServicoDAO->buscarSolicitacaoPeloId($id);

    if ($solicitacao) {
        $solicitacao['id'] = intval($solicitacao['id']);
        RespostaHttp::resposta('Solicitação encontrada com sucesso!', 200, $solicitacao, true);
    } else {
        RespostaHttp::resposta('Não existe uma solicitação cadastrada no banco de dados com esse id!');
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar a solicitação pelo id!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar a solicitação pelo id!', 200, null, false);
}