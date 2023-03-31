<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $cpf = trim(ParametroRequisicao::obterParametro('cpf'));
    $senha = trim(ParametroRequisicao::obterParametro('senha'));

    if (empty($cpf) || empty($senha)) {
        RespostaHttp::resposta('Informe o cpf e a senha!', 400, null);
        exit;
    }

    $senha = md5($senha);
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $cidadao = $cidadaoDAO->buscarPeloCpfSenha($cpf, $senha);

    if ($cidadao != false) {
        RespostaHttp::resposta('Cidadão encontrado com sucesso!', 200, $cidadao);
    } else {
        RespostaHttp::resposta('Não existe um cidadão cadastrado com esse cpf e senha!');
    }

} catch (Exception $e) {
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar o cidadão pelo cpf e senha!', 400, null);
}