<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\ServicoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    Auth::validarToken();

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id do tipo de serviço não foi informado para consulta!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);

    if (empty($id))  {
        RespostaHttp::resposta('Informe o id do tipo de serviço para realizar a consulta!', 200, null, false);
        exit;
    }

    // validando se o id do tipo de serviço é maior que 0
    if ($id < 0) {
        RespostaHttp::resposta('O id do tipo de serviço deve ser maior que 0!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tipoServicoDAO = new ServicoDAO($conexaoBancoDados, 'tbl_servicos');
    $tipoServico = $tipoServicoDAO->buscarPeloId($id);
    
    if (!$tipoServico) {
        RespostaHttp::resposta('Não existe um tipo de serviço cadastrado com esse id!');
        exit;
    }

    $tipoServico['id'] = intval($tipoServico['id']);
    RespostaHttp::resposta('Tipo de serviço encontrado com sucesso!', 200, $tipoServico);
} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar um tipo de serviço pelo id!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar um tipo de serviço pelo id!', 200, null, false);
}