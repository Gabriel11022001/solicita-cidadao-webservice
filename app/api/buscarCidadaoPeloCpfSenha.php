<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;

try {
    $cpf = trim(ParametroRequisicao::obterParametro('cpf'));
    $senha = trim(ParametroRequisicao::obterParametro('senha'));
    $errosCampos = [];

    if (empty($cpf)) {
        $errosCampos['cpf'] = 'Informe o cpf do cidadão!';
    }

    if (empty($senha)) {
        $errosCampos['senha'] = 'Informe a senha do cidadão!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Informe o cpf e a senha do cidadão!', 400, $errosCampos);
        exit;
    }

    // validando se o cpf informando é válido
    if (!ValidaCpf::validarCPF($cpf)) {
        $errosCampos['cpf'] = 'O cpf informado é inválido!';
    }

    // validando se a senha possui no mínimo 6 caracteres
    if (strlen($senha) < 6) {
        $errosCampos['senha'] = 'A senha deve possuir no mínimo 6 caracteres!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 400, $errosCampos);
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