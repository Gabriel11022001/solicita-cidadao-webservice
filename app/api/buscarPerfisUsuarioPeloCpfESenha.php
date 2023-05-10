<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorSecretariaDAO;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\SecretarioDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;

try {
    $errosParametrosRequisicao = [];

    if (!isset($_GET['cpf'])) {
        $errosParametrosRequisicao['cpf'] = 'O cpf não foi informado como um parâmetro na url da requisição!';
    }

    if (!isset($_GET['senha'])) {
        $errosParametrosRequisicao['senha'] = 'A senha não foi informada como um parâmetro na url da requisição!';
    }

    if (count($errosParametrosRequisicao) > 0) {
        RespostaHttp::resposta('Informe todos os parâmetros na url da requisição!', 200, $errosParametrosRequisicao, false);
        exit;
    }

    $cpf = trim($_GET['cpf']);
    $senha = trim($_GET['senha']);

    if (empty($cpf)) {
        $errosParametrosRequisicao['cpf'] = 'Informe o cpf do usuário!';
    }

    if (empty($senha)) {
        $errosParametrosRequisicao['senha'] = 'Informe a senha do usuário!';
    }

    if (count($errosParametrosRequisicao) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios para realizar a consulta!', 200, $errosParametrosRequisicao, false);
        exit;
    }

    if (!ValidaCpf::validarCPF($cpf)) {
        $errosParametrosRequisicao['cpf'] = 'O cpf informado é inválido!';
    }

    if (strlen($senha) < 6) {
        $errosParametrosRequisicao['senha'] = 'A senha deve possuir no mínimo 6 caracteres!';
    }

    if (count($errosParametrosRequisicao) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, $errosParametrosRequisicao, false);
        exit;
    }

    $senha = md5($senha);
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
    $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
    $gestorInstituicaoDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
    $gestorSecretariaDAO = new GestorSecretariaDAO($conexaoBancoDados, 'tbl_gestores_secretaria');
    $secretarioDAO = new SecretarioDAO($conexaoBancoDados, 'tbl_secretarios');
    $dadosPerfis = [];
    $dadosUsuarioPerfilCidadao = $cidadaoDAO->buscarPeloCpfSenha($cpf, $senha);
    $dadosUsuarioPerfilPerito = $peritoDAO->buscarPeloCpfSenha($cpf, $senha);
    $dadosUsuarioPerfilTecnico = $tecnicoDAO->buscarPeloCpfSenha($cpf, $senha);
    $dadosUsuarioPerfilSecretario = $secretarioDAO->buscarPeloCpfSenha($cpf, $senha);
    $dadosUsuarioPerfilGestorSecretaria = $gestorSecretariaDAO->buscarPeloCpfSenha($cpf, $senha);
    $dadosUsuarioPerfilGestorInstituicao = $gestorInstituicaoDAO->buscarPeloCpfSenha($cpf, $senha);

    if (count($dadosUsuarioPerfilCidadao) > 0) {
        $dadosPerfis['cidadao'] = $dadosUsuarioPerfilCidadao;
    }
    
    if (count($dadosUsuarioPerfilGestorInstituicao) > 0) {
        $dadosPerfis['gestor_instituicao'] = $dadosUsuarioPerfilGestorInstituicao;
    }

    if (count($dadosUsuarioPerfilGestorSecretaria) > 0) {
        $dadosPerfis['gestor_secretaria'] = $dadosUsuarioPerfilGestorSecretaria;
    }

    if (count($dadosUsuarioPerfilTecnico) > 0) {
        $dadosPerfis['tecnico'] = $dadosUsuarioPerfilTecnico;
    }

    if (count($dadosUsuarioPerfilSecretario) > 0) {
        $dadosPerfis['secretario'] = $dadosUsuarioPerfilSecretario;
    }

    if (count($dadosUsuarioPerfilPerito) > 0) {
        $dadosPerfis['perito'] = $dadosUsuarioPerfilPerito;
    }

    if (count($dadosPerfis) === 0) {
        RespostaHttp::resposta('Não existem perfis cadastrados com esse cpf e senha!');
        exit;
    }

    RespostaHttp::resposta('Foram encontrados perfis cadastrados com esse cpf e senha!', 200, $dadosPerfis);
} catch (Exception $e) {

}