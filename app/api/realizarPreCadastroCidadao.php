<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Entidades\Cidadao;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\GerenciadorEmail;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    Auth::validarToken();
    $cidadao = new Cidadao();
    $cidadao->setNome(trim(mb_strtoupper(ParametroRequisicao::obterParametro('nome'))));
    $cidadao->setSobrenome(trim(mb_strtoupper(ParametroRequisicao::obterParametro('sobrenome'))));
    $cidadao->setCpf(trim(ParametroRequisicao::obterParametro('cpf')));
    $cidadao->setTelefone(trim(ParametroRequisicao::obterParametro('telefone')));
    $cidadao->setEmail(trim(ParametroRequisicao::obterParametro('email')));
    $cidadao->setSenha(trim(ParametroRequisicao::obterParametro('senha')));
    $senhaConfirmacao = trim(ParametroRequisicao::obterParametro('senha_confirmacao'));
    $errosDados = [];

    if (empty($cidadao->getNome())) {
        $errosDados['nome'] = 'Informe o nome do cidadão!';
    }

    if (empty($cidadao->getSobrenome())) {
        $errosDados['sobrenome'] = 'Informe o sobrenome do cidadão!';
    }

    if (empty($cidadao->getCpf())) {
        $errosDados['cpf'] = 'Informe o cpf do cidadão!';
    }

    if (empty($cidadao->getTelefone())) {
        $errosDados['telefone'] = 'Informe o telefone do cidadão!';
    }

    if (empty($cidadao->getSenha())) {
        $errosDados['senha'] = 'Informe a senha do cidadão!';
    }

    if (empty($senhaConfirmacao)) {
        $errosDados['senha_confirmacao'] = 'Informe a senha de confirmação!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios para realizar o pré-cadastro do cidadão!', 200, $errosDados, false);
        exit;
    }

    // validando o cpf
    if (!ValidaCpf::validarCPF($cidadao->getCpf())) {
        $errosDados['cpf'] = 'O cpf informado é inválido!';
    }
    
    if (strlen($cidadao->getEmail()) > 0) {

        // validando o e-mail
        if (!ValidaEmail::validarEmail($cidadao->getEmail())) {
            $errosDados['email'] = 'O e-mail informado é inválido!';
        } elseif (mb_strlen($cidadao->getEmail()) > 255 || mb_strlen($cidadao->getEmail()) < 3) {
            $errosDados['email'] = 'O e-mail deve possuir no mínimo 3 caracteres e no máximo 255 caracteres!';
        }

    }

    // validando se o nome possui no mínimo 3 caracteres
    if (mb_strlen($cidadao->getNome()) < 3) {
        $errosDados['nome'] = 'O nome deve possuir no mínimo 3 caracteres!';
    } elseif (mb_strlen($cidadao->getNome()) > 255) {
        $errosDados['nome'] = 'O nome não deve possuir mais de 255 caracteres!';
    }

    // validando se o sobrenome possui no mínimo 3 caracteres
    if (mb_strlen($cidadao->getSobrenome()) < 3) {
        $errosDados['sobrenome'] = 'O sobrenome deve possuir no mínimo 3 caracteres!';
    } elseif (mb_strlen($cidadao->getSobrenome()) > 255) {
        $errosDados['sobrenome'] = 'O sobrenome não deve possuir mais que 255 caracteres!';
    }

    // validando a senha
    if ((mb_strlen($cidadao->getSenha()) < 6) || (mb_strlen($cidadao->getSenha()) > 25)) {
        $errosDados['senha'] = 'A senha deve possuir no mínimo 6 caracteres e no máximo 25 caracteres!';
    } elseif  ($cidadao->getSenha() != $senhaConfirmacao) {
        $errosDados['senha_confirmacao'] = 'A senha e a senha de confirmação devem ser iguais!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosDados, false);
        exit;
    }

    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');

    // validando se já existe outro cidadão cadastrado com o e-mail informado
    if ($cidadaoDAO->buscarCidadaoPeloEmail($cidadao->getEmail())) {
        RespostaHttp::resposta('Já existe outro cidadão cadastrado com esse e-mail, informe outro e-mail!', 200, null, false);
        exit;
    }

    // validando se já existe outro cidadão cadastrado com o cpf informado
    if ($cidadaoDAO->buscarPeloCpf($cidadao->getCpf())) {
        RespostaHttp::resposta('Já existe outro cidadão cadastrado com esse cpf, informe outro cpf!', 200, null, false);
        exit;
    }

    // cadastrando primeiro o usuário na tabela tbl_usuarios
    $dadosUsuarioCadastrar = [
        'nome' => [
            'dado' => $cidadao->getNome(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cpf' => [
            'dado' => $cidadao->getCpf(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'email' => [
            'dado' => $cidadao->getEmail(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'sobrenome' => [
            'dado' => $cidadao->getSobrenome(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'telefone' => [
            'dado' => $cidadao->getTelefone(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'senha' => [
            'dado' => md5($cidadao->getSenha()),
            'tipo_dado' => PDO::PARAM_STR
        ]
    ];

    if (!$usuarioDAO->salvar($dadosUsuarioCadastrar)) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão!', 200, null, false);
        exit;
    }

    $idUsuario = $conexaoBancoDados->lastInsertId();

    if (!$cidadaoDAO->salvar([
        'usuario_id' => [
            'dado' => $idUsuario,
            'tipo_dado' => PDO::PARAM_INT
        ]
    ])) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão!', 200, null, false);
        exit;
    }

    if (strlen($cidadao->getEmail()) > 0) {

        // enviando e-mail para o cidadão informando que foi realizado um pré-cadastro para ele no sistema
        if (!GerenciadorEmail::enviarEmail(
            $cidadao->getEmail(),
            'Você foi pré-cadastrado no sistema de solicitações de serviço! Acesse o sistema para poder continuar informando seus dados cadastrais!<br>
            Dados informados no cadastro:<br>
            <ul>
                <li>Nome: ' . $cidadao->getNome() . '</li>
                <li>Sobrenome: ' . $cidadao->getSobrenome() . '</li>
                <li>Telefone: ' . $cidadao->getTelefone() . '</li>
                <li>E-mail: ' . $cidadao->getEmail() . '</li>
                <li>Cpf: ' . $cidadao->getCpf() . '</li>
                <li>Senha para acessar o sistema: ' . $cidadao->getSenha() . '</li>
            </ul>',
            'Pré-cadastro'
        )) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão no envio de e-mail!', 200, null, false);
            exit;
        }

    }

    $conexaoBancoDados->commit();
    RespostaHttp::resposta('Cidadão cadastrado com sucesso!', 201, [
        'id' => $conexaoBancoDados->lastInsertId(),
        'nome' => $cidadao->getNome(),
        'email' => $cidadao->getEmail(),
        'sobrenome' => $cidadao->getSobrenome(),
        'telefone' => $cidadao->getTelefone(),
        'cpf' => $cidadao->getCpf()
    ], true);
} catch (AuthException $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog($e->getMessage(), $e->getMessage());
    RespostaHttp::resposta('Erro de autenticação!', 200, null, false);
} catch (Exception $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar o cidadão!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão!' . $e->getMessage(), 200, null, false);
}