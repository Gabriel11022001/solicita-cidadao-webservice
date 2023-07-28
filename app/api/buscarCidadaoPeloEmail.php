<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();

    if (!isset($_GET['email'])) {
        RespostaHttp::resposta('O e-mail não foi definido na url como parâmetro de consulta!', 200, null, false);
        exit;
    }

    $email = trim($_GET['email']);

    if (empty($email)) {
        RespostaHttp::resposta('Informe o e-mail!', 200, null, false);
        exit;
    }

    if (!ValidaEmail::validarEmail($email)) {
        RespostaHttp::resposta('O e-mail informado não é válido!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $cidadao = $cidadaoDAO->buscarCidadaoPeloEmail($email);

    if (!$cidadao) {
        RespostaHttp::resposta('Não existe um cidadão cadastrado com esse e-mail no banco de dados!');
    } else {
        $cidadao['id'] = intval($cidadao['id']);
        RespostaHttp::resposta('Cidadão encontrado com sucesso!', 200, $cidadao);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar o cidadão pelo e-mail!', $e->getMessage());
}