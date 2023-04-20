<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Entidades\TipoServico;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $tipoServico = new TipoServico();
    $tipoServico->setId(ParametroRequisicao::obterParametro('id'));
    $tipoServico->setStatus(ParametroRequisicao::obterParametro('novo_status'));

    if (empty($tipoServico->getId())) {
        RespostaHttp::resposta('É obrigatório informar o id do tipo de serviço para editar o mesmo!', 400, null);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    
    // validando se existe um tipo de serviço cadastrado com o id informado
    if (!$tipoServicoDAO->buscarPeloId($tipoServico->getId())) {
        RespostaHttp::resposta('Não existe um tipo de serviço cadastrado com o id informado!', 400, null);
        exit;
    }

    if ($tipoServicoDAO->alterarStatusTipoServico($tipoServico)) {
        RespostaHttp::resposta('O status do tipo de serviço foi alterado com sucesso!', 200, [
            'id' => $tipoServico->getId(),
            'novo_status' => $tipoServico->getStatus()
        ]);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar o status do tipo de serviço!', 400, null);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se alterar o status do tipo de serviço!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar o status do tipo de serviço!', 400, null);
}