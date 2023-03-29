<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCep;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaNumeroResidencial;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaUF;

try {
    $nome = trim(ParametroRequisicao::obterParametro('nome'));
    $sobrenome = trim(ParametroRequisicao::obterParametro('sobrenome'));
    $email = trim(ParametroRequisicao::obterParametro('email'));
    $telefone = trim(ParametroRequisicao::obterParametro('telefone'));
    $cpf = trim(ParametroRequisicao::obterParametro('cpf'));
    $sexo = trim(ParametroRequisicao::obterParametro('sexo'));
    $dataNascimento = trim(ParametroRequisicao::obterParametro('data_nascimento'));
    $logradouro = trim(ParametroRequisicao::obterParametro('logradouro'));
    $cep = trim(ParametroRequisicao::obterParametro('cep'));
    $complemento = trim(ParametroRequisicao::obterParametro('complemento'));
    $bairro = trim(ParametroRequisicao::obterParametro('bairro'));
    $numero = trim(ParametroRequisicao::obterParametro('numero_residencia'));
    $cidade = trim(ParametroRequisicao::obterParametro('cidade'));
    $unidadeFederativa = trim(ParametroRequisicao::obterParametro('uf'));
    $senha = trim(ParametroRequisicao::obterParametro('senha'));
    $senhaConfirmacao = trim(ParametroRequisicao::obterParametro('senha_confirmacao'));
    $dataCadastro = new DateTime('now');

    // VALIDANDO SE TODOS OS DADOS OBRIGATÓRIOS FORAM INFORMADOS
    if (empty($nome) || empty($sobrenome) || empty($email)
    || empty($telefone) || empty($cpf) || empty($sexo) || empty($dataNascimento)
    || empty($logradouro) || empty($cep) || empty($bairro) || empty($cidade) 
    || empty($unidadeFederativa) || empty($senha) || empty($senhaConfirmacao)
    || empty($numero)) {
        RespostaHttp::resposta('Preencha todos os campos obrigatórios!');
        exit;
    }

    // VALIDANDO O CPF DO CIDADÃO
    if (!ValidaCpf::validarCPF($cpf)) {
        RespostaHttp::resposta('O cpf do cidadão é inválido!', null, 400);
        exit;
    }

    // VALIDANDO O E-MAIL DO CIDADÃO
    if (!ValidaEmail::validarEmail($email)) {
        RespostaHttp::resposta('O e-mail do cidadão é inválido!', null, 400);
        exit;
    }

    // VALIDANDO O CEP
    if (!ValidaCep::validarCep($cep)) {
        RespostaHttp::resposta('O cep do cidadão é inválido!', null, 400);
        exit;
    }

    // VALIDANDO A UNIDADE FEDERATIVA
    if (!ValidaUF::validarUf($unidadeFederativa)) {
        RespostaHttp::resposta('Unidade federativa inválida!', null, 400);
        exit;
    }

    // VALIDANDO O NÚMERO RESIDENCIAL
    if (!ValidaNumeroResidencial::validarNumeroResidencial($numero)) {
        RespostaHttp::resposta('Número residencial inválido! Caso não possua um número residencial, deve ser '
        . 'informado "s/n", caso possua um número residencial, o mesmo deve ser um valor numérico inteiro!', null, 400);
        exit;
    }

    // VALIDANDO SE A SENHA E A SENHA DE CONFIRMAÇÃO SÃO IGUAIS
    if ($senha != $senhaConfirmacao) {
        RespostaHttp::resposta('A senha e a senha de confirmação são diferentes!', null, 400);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');

    // VALIDANDO SE JÁ EXISTE OUTRO CIDADÃO CADASTRADO COM O CPF INFORMADO
    if ($cidadaoDAO->buscarCidadaoPeloCpf($cpf) != false) {
        RespostaHttp::resposta('Já existe outro cidadão cadastrado com esse cpf!', null, 400);
        exit;
    }

    // VALIDANDO SE JÁ EXISTE OUTRO CIDADÃO CADASTRADO COM O E-MAIL INFORMADO
    if ($cidadaoDAO->buscarCidadaoPeloEmail($email) != false) {
        RespostaHttp::resposta('Já existe outro cidadão cadastrado com esse e-mail!', null, 400);
        exit;
    }

    $nome = strtoupper($nome);
    $sobrenome = strtoupper($sobrenome);
    $dataNascimentoCadastrar = new DateTime($dataNascimento);
    $senha = md5($senha);
    $dadosCidadaoCadastrar = [
        'nome' => ['dado' => $nome, 'tipo_dado' => PDO::PARAM_STR],
        'cpf' => ['dado' => $cpf, 'tipo_dado' => PDO::PARAM_STR],
        'email' => ['dado' => $email, 'tipo_dado' => PDO::PARAM_STR],
        'sobrenome' => ['dado' => $sobrenome, 'tipo_dado' => PDO::PARAM_STR],
        'telefone' => ['dado' => $telefone, 'tipo_dado' => PDO::PARAM_STR],
        'sexo' => ['dado' => $sexo, 'tipo_dado' => PDO::PARAM_STR],
        'data_nascimento' => ['dado' => $dataNascimentoCadastrar->format('Y-m-d'), 'tipo_dado' => PDO::PARAM_STR],
        'senha' => ['dado' => $senha, 'tipo_dado' => PDO::PARAM_STR],
        'logradouro' => ['dado' => $logradouro, 'tipo_dado' => PDO::PARAM_STR],
        'complemento' => ['dado' => $complemento, 'tipo_dado' => PDO::PARAM_STR],
        'cidade' => ['dado' => $cidade, 'tipo_dado' => PDO::PARAM_STR],
        'bairro' => ['dado' => $bairro, 'tipo_dado' => PDO::PARAM_STR],
        'uf' => ['dado' => $unidadeFederativa, 'tipo_dado' => PDO::PARAM_STR],
        'numero_residencial' => ['dado' => $numero, 'tipo_dado' => PDO::PARAM_STR],
        'cep' => ['dado' => $cep, 'tipo_dado' => PDO::PARAM_STR],
        'data_cadastro' => ['dado' => $dataCadastro->format('Y-m-d H:i:s'), 'tipo_dado' => PDO::PARAM_STR]
    ];

    if ($cidadaoDAO->salvar($dadosCidadaoCadastrar)) {
        // cidadão cadastrado com sucesso
        $idCidadaoCadastrado = intval($conexaoBancoDados->lastInsertId());
        echo $idCidadaoCadastrado . PHP_EOL;
        $cidadaoCadastrado = $cidadaoDAO->buscarPeloId($idCidadaoCadastrado);
        RespostaHttp::resposta('Cidadão cadastrado com sucesso!', 201, $cidadaoCadastrado);
    } else {
        // ocorreu um erro ao tentar-se cadastrar o cidadão no banco de dados
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão no banco de dados!', 400);
    }
    
} catch (Exception $e) {
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão no banco de dados!: ' . $e->getMessage(), 400);
}