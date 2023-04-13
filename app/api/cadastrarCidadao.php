<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCep;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;

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
    $errosDeCamposObrigatorios = ValidaCamposObrigatorios::validarFormularioCadastroCidadao(
        $nome,
        $sobrenome,
        $email,
        $telefone,
        $cpf,
        $sexo,
        $dataNascimento,
        $logradouro,
        $cep,
        $bairro,
        $cidade,
        $unidadeFederativa,
        $senha,
        $senhaConfirmacao
    );

    if (count($errosDeCamposObrigatorios) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 400, $errosDeCamposObrigatorios);
        exit;
    }

    // VALIDANDO O CPF DO CIDADÃO
    if (!ValidaCpf::validarCPF($cpf)) {
        RespostaHttp::resposta('O cpf do informado é inválido!', 400, null);
        exit;
    }

    // VALIDANDO O E-MAIL DO CIDADÃO
    if (!ValidaEmail::validarEmail($email)) {
        RespostaHttp::resposta('O e-mail informado é inválido!', 400, null);
        exit;
    }

    // VALIDANDO O CEP
    if (!ValidaCep::validarCep($cep)) {
        RespostaHttp::resposta('O cep informado é inválido!', 400, null);
        exit;
    }

    // VALIDANDO SE A SENHA E A SENHA DE CONFIRMAÇÃO SÃO IGUAIS
    if ($senha != $senhaConfirmacao) {
        RespostaHttp::resposta('A senha e a senha de confirmação são diferentes!', 400, null);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');

    // VALIDANDO SE JÁ EXISTE OUTRO CIDADÃO CADASTRADO COM O CPF INFORMADO
    if ($cidadaoDAO->buscarPeloCpf($cpf) != false) {
        RespostaHttp::resposta('Já existe outro cidadão cadastrado com esse cpf!', 400, null);
        exit;
    }

    // VALIDANDO SE JÁ EXISTE OUTRO CIDADÃO CADASTRADO COM O E-MAIL INFORMADO
    if ($cidadaoDAO->buscarCidadaoPeloEmail($email) != false) {
        RespostaHttp::resposta('Já existe outro cidadão cadastrado com esse e-mail!', 400, null);
        exit;
    }
    
    $numero = strtolower($numero);

    if (empty($numero)) {
        $numero = 's/n';
    } elseif (!is_numeric($numero)) {
        $numero = 's/n';
    } elseif (intval($numero) <= 0) {
        $numero = 's/n';
    } else {
        $numeroFloat = floatval($numero);
        
        if ($numeroFloat > 0) {
            $numero = 's/n';
        }

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
        $cidadaoCadastrado = $cidadaoDAO->buscarPeloId($idCidadaoCadastrado);
        RespostaHttp::resposta('Cidadão cadastrado com sucesso!', 201, $cidadaoCadastrado);
    } else {
        // ocorreu um erro ao tentar-se cadastrar o cidadão no banco de dados
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão no banco de dados!', 400, null);
    }
    
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar um cidadão!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão no banco de dados!: ' . $e->getMessage(), 400, null);
}