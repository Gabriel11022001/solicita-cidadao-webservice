<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;

try {
    Auth::validarToken();
    
    if (!isset($_GET['cpf'])) {
        RespostaHttp::resposta('O cpf não está definido como parâmetro na url!', 200, null, false);
        exit;
    }

    $cpf = trim($_GET['cpf']);
    
    if (empty($cpf)) {
        RespostaHttp::resposta('O cpf é obrigatório para consultar o cidadão por meio dele!', 200, null, false);
        exit;
    }

    if (!ValidaCpf::validarCPF($cpf)) {
        RespostaHttp::resposta('O cpf informado é inválido!', 200, null, false);
    } else {
        $conexaoBancoDados = ConexaoBancoDados::obterConexao();
        $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
        $cidadao = $cidadaoDAO->buscarPeloCpf($cpf);

        if ($cidadao != false) {
            RespostaHttp::resposta('Cidadão encontrado com sucesso!', 200, $cidadao);
        } else {    
            RespostaHttp::resposta('Não existe um cidadão cadastrado com esse cpf!');
        }

    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar um cidadão pelo cpf!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar o cidadão pelo cpf!', 200, null, false);
}