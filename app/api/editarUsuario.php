<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\GestorSecretariaDAO;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Entidades\Endereco;
use SistemaSolicitacaoServico\App\Entidades\Usuario;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCep;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaDataNascimento;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaUF;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    $tipoUsuarioEditar = trim(ParametroRequisicao::obterParametro('tipo_usuario_editar'));
    $usuario = new Usuario();
    $usuario->setId(intval(ParametroRequisicao::obterParametro('id')));
    $usuario->setNome(mb_strtoupper(trim(ParametroRequisicao::obterParametro('nome'))));
    $usuario->setSobrenome(mb_strtoupper(trim(ParametroRequisicao::obterParametro('sobrenome'))));
    $usuario->setTelefone(trim(ParametroRequisicao::obterParametro('telefone')));
    $usuario->setEmail(trim(ParametroRequisicao::obterParametro('email')));
    $usuario->setSexo(trim(ParametroRequisicao::obterParametro('sexo')));
    $usuario->setDataNascimento(trim(ParametroRequisicao::obterParametro('data_nascimento')));
    $usuario->setStatus(boolval(ParametroRequisicao::obterParametro('status')));
    $usuario->setCpf(trim(ParametroRequisicao::obterParametro('cpf')));
    $endereco = new Endereco();
    $endereco->setLogradouro(trim(ParametroRequisicao::obterParametro('logradouro')));
    $endereco->setComplemento(trim(ParametroRequisicao::obterParametro('complemento')));
    $endereco->setCidade(trim(ParametroRequisicao::obterParametro('cidade')));
    $endereco->setBairro(trim(ParametroRequisicao::obterParametro('bairro')));
    $endereco->setEstado(trim(ParametroRequisicao::obterParametro('estado')));
    $endereco->setNumero(trim(ParametroRequisicao::obterParametro('numero_residencia')));
    $endereco->setCep(trim(ParametroRequisicao::obterParametro('cep')));
    $usuario->setEndereco($endereco);
    $idInstituicao = null;
    $errosCampos = ValidaCamposObrigatorios::validarFormularioEditarUsuario($usuario);

    // validando se foi informado o tipo do usuário que será editado
    if (empty($tipoUsuarioEditar)) {
        $errosCampos['tipo_usuario_editar'] = 'Informe o tipo do usuário que será editado!';
    } elseif ($tipoUsuarioEditar != 'cidadão' && $tipoUsuarioEditar != 'gestor-secretaria'
    && $tipoUsuarioEditar != 'técnico' && $tipoUsuarioEditar != 'perito'
    && $tipoUsuarioEditar != 'gestor-instituição' && $tipoUsuarioEditar != 'secretário') {
        $errosCampos['tipo_usuario_editar'] = 'Valor inválido para informar o tipo de usuário que será editado!';
    } else {

        if ($tipoUsuarioEditar === 'gestor-instituição' || $tipoUsuarioEditar === 'técnico') {
            $idInstituicao = intval(ParametroRequisicao::obterParametro('id_instituicao'));

            // validando se foi informado o id da instituição
            if (empty($idInstituicao)) {
                $errosCampos['instituicao_id'] = 'Informe o id da instituição a qual o usuário faz parte!';
            }

        }

    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosCampos, false);
        exit;
    }

    // validando o cpf
    if (!ValidaCpf::validarCPF($usuario->getCpf())) {
        $errosCampos['cpf'] = 'O cpf informado é inválido!';
    }
    
    // validando o e-mail
    if (!ValidaEmail::validarEmail($usuario->getEmail())) {
        $errosCampos['email'] = 'O e-mail informado é inválido!';
    } elseif (mb_strlen($usuario->getEmail()) > 255 || mb_strlen($usuario->getEmail()) < 3) {
        $errosCampos['email'] = 'O e-mail deve possuir no mínimo 3 caracteres e no máximo 255 caracteres!';
    }

    // validando a unidade federativa informada
    if (!ValidaUF::validarUF($endereco->getEstado())) {
        $errosCampos['uf'] = 'O estado informado é inválido!';
    }

    // validando o cep informado
    if (!ValidaCep::validarCep($endereco->getCep())) {
        $errosCampos['cep'] = 'O cep informado é inválido!';
    }

    // validando a data de nascimento
    if (!ValidaDataNascimento::validarFormatoDataNascimento($usuario->getDataNascimento())) {
        // o formato da data de nascimento é inválido
        $errosCampos['data_nascimento'] = 'Data de nascimento inválida!';
    } else {
        $dataNascimento = new DateTime($usuario->getDataNascimento());

        if (ValidaDataNascimento::validarSeDataNascimentoEhPosteriorADataAtual($dataNascimento)) {
            // A data de nascimento é posterior a data atual
            $errosCampos['data_nascimento'] = 'A data de nascimento não pode ser posterior a data atual!';
        } elseif (ValidaDataNascimento::validarSeDataNascimentoEhMuitoAntiga($dataNascimento)) {
            // a data de nascimento é muito antiga
            $errosCampos['data_nascimento'] = 'A data de nascimento é muito antiga!';
        } else {
            // a data de nascimento está ok
            $usuario->setDataNascimento($dataNascimento);
        }

    }

    // validando se o nome possui no mínimo 3 caracteres
    if (mb_strlen($usuario->getNome()) < 3) {
        $errosCampos['nome'] = 'O nome deve possuir no mínimo 3 caracteres!';
    } elseif (mb_strlen($usuario->getNome()) > 255) {
        $errosCampos['nome'] = 'O nome deve possuir no máximo 255 caracteres!';
    }

    // validando se o sobrenome possui no mínimo 3 caracteres
    if (mb_strlen($usuario->getSobrenome()) < 3) {
        $errosCampos['nome'] = 'O sobrenome deve possuir no mínimo 3 caracteres!';
    } elseif (mb_strlen($usuario->getSobrenome()) > 255) {
        $errosCampos['sobrenome'] = 'O sobrenome deve possuir no máximo 255 caracteres!';
    }

    // validando o número de residência
    if ($usuario->getEndereco()->getNumero() === '' || $usuario->getEndereco()->getNumero() === 'S/N') {
        $usuario->getEndereco()->setNumero('s/n');
    } else {
        if (!is_numeric($usuario->getEndereco()->getNumero())) {
            $numeroResTodoMinusculo = mb_strtolower($usuario->getEndereco()->getNumero());
    
            if ($numeroResTodoMinusculo != 's/n') {
                $errosCampos['numero_residencia'] = 'Caso você não possua um número de residência, informe s/n!';
            }

        } else {

            if (mb_strlen($usuario->getEndereco()->getNumero()) > 255) {
                $errosCampos['numero_residencia'] = 'O número de residência não deve possuir mais de 255 caracteres!';
            } else {
                $usuario->getEndereco()->setNumero(intval($usuario->getEndereco()->getNumero()));
    
                if ($usuario->getEndereco()->getNumero() <= 0) {
                    $errosCampos['numero_residencia'] = 'O número de residência não deve ser menor ou igual a zero!';
                }

            }
    
        }
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosCampos, false);
        exit;
    }

    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');
    $tecnicoDAO = null;
    $gestorInstituicaoDAO = null;
    $usuarioComIdInformado = $usuarioDAO->buscarPeloId($usuario->getId());
    
    if (!$usuarioComIdInformado) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Não existe um usuário cadastrado com esse id!', 200, null, false);
        exit;
    }
    
    if ($tipoUsuarioEditar === 'técnico') {
        $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
    } elseif ($tipoUsuarioEditar === 'gestor-instituição') {
        $gestorInstituicaoDAO = new GestorSecretariaDAO($conexaoBancoDados, 'tbl_gestores_instituicoes');
    }
    
} catch (AuthException $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog($e->getMessage(), $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar os dados do usuário!', 200, null, false);
} catch (Exception $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se editar os dados do usuário!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar os dados do usuário!', 200, null, false);
}