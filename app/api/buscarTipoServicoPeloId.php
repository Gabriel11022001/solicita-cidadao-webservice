<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $id = ParametroRequisicao::obterParametro('id');

    if (empty($id))  {
        RespostaHttp::resposta('Informe o id do tipo de serviço para realizar a consulta!', 400, null);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    $tipoServico = $tipoServicoDAO->buscarPeloId($id);

    if (!$tipoServico) {
        RespostaHttp::resposta('Não existe um tipo de serviço cadastrado com esse id!', 200, null);
        exit;
    }

    $tipoServico['id'] = intval($tipoServico['id']);
    RespostaHttp::resposta('Tipo de serviço encontrado com sucesso!', 200, $tipoServico);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar um tipo de serviço pelo id!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar um tipo de serviço pelo id!', 400, null);
}