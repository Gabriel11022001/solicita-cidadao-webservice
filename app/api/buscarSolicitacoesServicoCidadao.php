<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    
    if (!isset($_GET['cidadao_id'])) {
        RespostaHttp::resposta('O id do cidadão não foi definido como parâmetro na url!', 200, null, false);
        exit;
    }

    $idCidadao = intval($_GET['cidadao_id']);

    if (empty($idCidadao)) {
        RespostaHttp::resposta('Informe o id do cidadão!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    
    if (!$cidadaoDAO->buscarPeloId($idCidadao)) {
        RespostaHttp::resposta('Não existe um cidadão cadastrado com esse id!');
        exit;
    }

    $solicitacoes = $solicitacaoServicoDAO->buscarTodasSolicitacoesServicoCidadao($idCidadao);

    if (count($solicitacoes) === 0) {
        RespostaHttp::resposta('Não existem solicitações de serviço cadastradas!', 200, [], true);
    } else {

        foreach ($solicitacoes as $solicitacao) {
            $solicitacao['id'] = intval($solicitacao['id']);
        }

        if (count($solicitacoes) === 1) {
            RespostaHttp::resposta('Você possui uma solicitação de serviço!', 200, $solicitacoes, true);
        } else {
            RespostaHttp::resposta('Você possui um total de ' . count($solicitacoes) . ' solicitações de serviço!', 200, $solicitacoes, true);
        }

    }

} catch (Exception $e) {
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se consultar as solicitações de serviço!', 200, null, false);
}