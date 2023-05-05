<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;

try {
    $errosCampos = [];

    if (!isset($_GET['cpf'])) {
        $errosCampos['cpf'] = 'O cpf do cidadão não está definido na url para consulta!';
    }

    if (!isset($_GET['senha'])) {
        $errosCampos['senha'] = 'A senha do cidadão não está definida na url para consulta!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Informe todos os dados na url para consulta!', 200, $errosCampos, false);
        exit;
    }

    $cpf = trim($_GET['cpf']);
    $senha = trim($_GET['senha']);

    if (empty($cpf)) {
        $errosCampos['cpf'] = 'Informe o cpf do cidadão!';
    }

    if (empty($senha)) {
        $errosCampos['senha'] = 'Informe a senha do cidadão!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Informe o cpf e a senha do cidadão!', 200, $errosCampos, false);
        exit;
    }

    // validando se o cpf informado é válido
    if (!ValidaCpf::validarCPF($cpf)) {
        $errosCampos['cpf'] = 'O cpf informado é inválido!';
    }

    // validando se a senha possui no mínimo 6 caracteres
    if (strlen($senha) < 6) {
        $errosCampos['senha'] = 'A senha deve possuir no mínimo 6 caracteres!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosCampos, false);
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
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar o cidadão pelo cpf e senha!', 200, null, false);
}