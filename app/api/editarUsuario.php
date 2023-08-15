<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\GestorSecretariaDAO;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\DAOS\SecretarioDAO;
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
    Auth::validarToken();
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
    
    if ($tipoUsuarioEditar === 'técnico' || $tipoUsuarioEditar === 'gestor-instituição') {
        // validando se existe uma instituição cadastrada com o id informado
        $instituicaoComIdInformado = $instituicaoDAO->buscarPeloId($idInstituicao);

        if (!$instituicaoComIdInformado) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Não existe uma instituição cadastrada com o id informado!', 200, null, false);
            exit;
        }

        if (!$instituicaoComIdInformado['status']) {
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('A instituição em questão não está ativa!', 200, null, false);
            exit;
        }

    }

    $tipoExpecificoUsuarioEditarDAO = null;

    if ($tipoUsuarioEditar === 'cidadão') {
        $tipoExpecificoUsuarioEditarDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
        $cidadaoComEmailInformado = $tipoExpecificoUsuarioEditarDAO->buscarCidadaoPeloEmail($usuario->getEmail());
        $cidadaoComCpfInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloCpf($usuario->getCpf());

        if ($cidadaoComEmailInformado) {

            if ($cidadaoComEmailInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro cidadão cadastrado com o e-mail informado!', 200, null, false);
                exit;
            }

        }

        if ($cidadaoComCpfInformado) {

            if ($cidadaoComCpfInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro cidadão cadastrado com o cpf informado!', 200, null, false);
                exit;
            }

        }

    }

    if ($tipoUsuarioEditar === 'perito') {
        $tipoExpecificoUsuarioEditarDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
        $peritoComEmailInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloEmail($usuario->getEmail());
        $peritoComCpfInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloCpf($usuario->getCpf());

        if ($peritoComEmailInformado) {

            if ($peritoComEmailInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro perito cadastrado com o e-mail informado!', 200, null, false);
                exit;
            }

        }

        if ($peritoComCpfInformado) {

            if ($peritoComCpfInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro perito cadastrado com o cpf informado!', 200, null, false);
                exit;
            }

        }

    }

    if ($tipoUsuarioEditar === 'técnico') {
        $tipoExpecificoUsuarioEditarDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
        $tecnicoComEmailInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloEmail($usuario->getEmail());
        $tecnicoComCpfInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloCpf($usuario->getCpf());

        if ($tecnicoComEmailInformado) {

            if ($tecnicoComEmailInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro técnico cadastrado com o e-mail informado!', 200, null, false);
                exit;
            }

        }

        if ($tecnicoComCpfInformado) {

            if ($tecnicoComCpfInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro ténico cadastrado com o cpf informado!', 200, null, false);
                exit;
            }

        }

    }

    if ($tipoUsuarioEditar === 'secretário') {
        $tipoExpecificoUsuarioEditarDAO = new SecretarioDAO($conexaoBancoDados, 'tbl_secretarios');
        $secretarioComEmailInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloEmail($usuario->getEmail());
        $secretarioComCpfInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloCpf($usuario->getCpf());

        if ($secretarioComEmailInformado) {

            if ($secretarioComEmailInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro(a) secretário(a) com o e-mail informado!', 200, null, false);
                exit;
            }

        }

        if ($secretarioComCpfInformado) {

            if ($secretarioComCpfInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro(a) secretário(a) com o cpf informado!', 200, null, false);
                exit;
            }

        }

    }

    if ($tipoUsuarioEditar === 'gestor-instituição') {
        $tipoExpecificoUsuarioEditarDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
        $gestorInstituicaoComEmailInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloEmail($usuario->getEmail());
        $gestorInstituicaoComCpfInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloCpf($usuario->getCpf());

        if ($gestorInstituicaoComEmailInformado) {

            if ($gestorInstituicaoComEmailInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro gestor de instituição cadastrado com o e-mail informado!', 200, null, false);
                exit;
            }

        }

        if ($gestorInstituicaoComCpfInformado) {

            if ($gestorInstituicaoComCpfInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro gestor de instituição cadastrado com o cpf informado!', 200, null, false);
                exit;
            }

        }

    }

    if ($tipoUsuarioEditar === 'gestor-secretaria') {
        $tipoExpecificoUsuarioEditarDAO = new GestorSecretariaDAO($conexaoBancoDados, 'tbl_gestores_secretaria');
        $gestorSecretariaComEmailInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloEmail($usuario->getEmail());
        $gestorSecretariaComCpfInformado = $tipoExpecificoUsuarioEditarDAO->buscarPeloCpf($usuario->getCpf());

        if ($gestorSecretariaComEmailInformado) {

            if ($gestorSecretariaComEmailInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro gestor de secretaria cadastrado com o e-mail informado!', 200, null, false);
                exit;
            }

        }

        if ($gestorSecretariaComCpfInformado) {

            if ($gestorSecretariaComCpfInformado['usuario_id'] != $usuario->getId()) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Já existe outro gestor de secretaria cadastrado com o cpf informado!', 200, null, false);
                exit;
            }

        }

    }

    $dadosUsuarioEditar = [
        'nome' => [
            'dado' => $usuario->getNome(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'sobrenome' => [
            'dado' => $usuario->getSobrenome(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'telefone' => [
            'dado' => $usuario->getTelefone(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'email' => [
            'dado' => $usuario->getEmail(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cpf' => [
            'dado' => $usuario->getCpf(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'sexo' => [
            'dado' => $usuario->getSexo(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'data_nascimento' => [
            'dado' => $usuario->getDataNascimento()->format('Y-m-d H:i:s'),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'status' => [
            'dado' => $usuario->getStatus(),
            'tipo_dado' => PDO::PARAM_BOOL
        ],
        'logradouro' => [
            'dado' => $usuario->getEndereco()->getLogradouro(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'complemento' => [
            'dado' => $usuario->getEndereco()->getComplemento(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cidade' => [
            'dado' => $usuario->getEndereco()->getCidade(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'bairro' => [
            'dado' => $usuario->getEndereco()->getBairro(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'estado' => [
            'dado' => $usuario->getEndereco()->getEstado(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'numero_residencia' => [
            'dado' => $usuario->getEndereco()->getNumero(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cep' => [
            'dado' => $usuario->getEndereco()->getCep(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'id' => [
            'dado' => $usuario->getId(),
            'tipo_dado' => PDO::PARAM_INT
        ]
    ];

    if (!$usuarioDAO->editar($dadosUsuarioEditar)) {
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar os dados cadastrais do usuário!', 200, null, false);
        exit;
    }

    if ($tipoUsuarioEditar === 'técnico' || $tipoUsuarioEditar === 'gestor-instituição') {
        $tabela = '';

        if ($tipoUsuarioEditar === 'técnico') {
            $tabela = 'tbl_tecnicos';
        } else {
            $tabela = 'tbl_gestores_instituicao';
        }

        if ($tabela === 'tbl_tecnicos') {

            if (!$tecnicoDAO->alterarIdInstituicao($usuario->getId(), $idInstituicao, $tabela)) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar os dados do usuário!', 200, null, false);
                exit;
            }

        } else {

            if (!$gestorInstituicaoDAO->alterarIdInstituicao($usuario->getId(), $idInstituicao, $tabela)) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar os dados do usuário!', 200, null, false);
                exit;
            }

        }

    }

    $conexaoBancoDados->commit();
    RespostaHttp::resposta('Os dados do usuário foram alterados com sucesso!', 200, [
        'id' => $usuario->getId(),
        'nome' => $usuario->getNome(),
        'sobrenome' => $usuario->getSobrenome(),
        'telefone' => $usuario->getTelefone(),
        'email' => $usuario->getEmail(),
        'cpf' => $usuario->getCpf(),
        'sexo' => $usuario->getSexo(),
        'data_nascimento' => $usuario->getDataNascimento()->format('d-m-Y H:i:s'),
        'status' => $usuario->getStatus(),
        'cep' => $usuario->getEndereco()->getCep(),
        'logradouro' => $usuario->getEndereco()->getLogradouro(),
        'complemento' => $usuario->getEndereco()->getComplemento(),
        'cidade' => $usuario->getEndereco()->getCidade(),
        'bairro' => $usuario->getEndereco()->getBairro(),
        'estado' => $usuario->getEndereco()->getEstado(),
        'numero_residencia' => $usuario->getEndereco()->getNumero()
    ], true);
} catch (AuthException $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se editar os dados do usuário!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar os dados do usuário!', 200, null, false);
}