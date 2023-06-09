<?php

use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {

    if (!isset($_GET['id_perito'])) {
        RespostaHttp::resposta('O id do perito deve ser passado na url como parâmetro!', 200, null, false);
        exit;
    }
    
    $id = intval($_GET['id_perito']);

    if (empty($id)) {
        RespostaHttp::resposta('Informe o id do perito!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');

    if (!$peritoDAO->buscarUsuarioPeloId($id)) {
        RespostaHttp::resposta('Não existe um perito cadastrado no banco de dados com esse id!', 200, null, false);
        exit;
    }

    $peritoId = $peritoDAO->obterIdPeritoPeloIdUsuario($id)['id'];
    $solicitacoes = $solicitacaoServicoDAO->buscarSolicitacoesServicoPerito($peritoId);

    if (count($solicitacoes) === 0) {
        RespostaHttp::resposta('Não existem solicitações cadastradas no banco de dados!', 200, null, false);
        exit;
    }

    foreach ($solicitacoes as $solicitacao) {
        $solicitacao['id'] = intval($solicitacao['id']);
    }

    if (count($solicitacoes) === 1) {
        RespostaHttp::resposta('Existe 1 solicitação cadastrada no banco de dados!', 200, $solicitacoes, true);
    } else {
        RespostaHttp::resposta('Existe um total de ' . count($solicitacoes) . ' solicitações cadastradas no banco de dados!', 200, $solicitacoes, true);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar as solicitações do perito em questão!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar as solicitações do perito em questão!' . $e->getMessage(), 200, null, false);
}