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
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCep;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaDataNascimento;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaUF;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
// iniciando a transação
$conexaoBancoDados->beginTransaction();

try {
    Auth::validarToken();
    $usuario = new Usuario();
    $endereco = new Endereco();
    /**
     * números relacionados aos tipos de usuário
     * 1 - cidadão
     * 2 - gestor da secretaria
     * 3 - gestor de instituição
     * 4 - perito
     * 5 - ténico
     * 6 - secretário(a)
     */
    $tipoUsuarioCadastrar = intval(ParametroRequisicao::obterParametro('tipo_usuario_cadastrar'));
    $usuario->setNome(trim(ParametroRequisicao::obterParametro('nome')));
    $usuario->setSobrenome(trim(ParametroRequisicao::obterParametro('sobrenome')));
    $usuario->setTelefone(trim(ParametroRequisicao::obterParametro('telefone')));
    $usuario->setCpf(trim(ParametroRequisicao::obterParametro('cpf')));
    $usuario->setEmail(trim(ParametroRequisicao::obterParametro('email')));
    $usuario->setSenha(trim(ParametroRequisicao::obterParametro('senha')));
    $senhaConfirmacao = trim(ParametroRequisicao::obterParametro('senha_confirmacao'));
    $usuario->setStatus(ParametroRequisicao::obterParametro('status'));
    $usuario->setSexo(trim(ParametroRequisicao::obterParametro('sexo')));
    $dataNascimentoUsuario = trim(ParametroRequisicao::obterParametro('data_nascimento'));
    $endereco->setCep(trim(ParametroRequisicao::obterParametro('cep')));
    $endereco->setLogradouro(trim(ParametroRequisicao::obterParametro('logradouro')));
    $endereco->setComplemento(trim(ParametroRequisicao::obterParametro('complemento')));
    $endereco->setBairro(trim(ParametroRequisicao::obterParametro('bairro')));
    $endereco->setCidade(trim(ParametroRequisicao::obterParametro('cidade')));
    $endereco->setEstado(trim(ParametroRequisicao::obterParametro('uf')));
    $endereco->setNumero(trim(ParametroRequisicao::obterParametro('numero_residencia')));
    $idDaInstituicao = null;
    $usuario->setEndereco($endereco);
    $errosDadosUsuario = ValidaCamposObrigatorios::validarFormularioCadastrarUsuario($usuario, $tipoUsuarioCadastrar);
    
    // validando se foi informado o tipo do usuário que será cadastrado
    if (empty($tipoUsuarioCadastrar)) {
        $errosDadosUsuario['tipo_usuario_cadastrar'] = 'Informe o tipo do usuário que será cadastrado!';
    } elseif ($tipoUsuarioCadastrar < 1 || $tipoUsuarioCadastrar > 6) {
        // validando se o número correspondente ao tipo de usuário informado é válido
        $errosDadosUsuario['tipo_usuario_cadastrar'] = 'O número do tipo de usuário a ser cadastrado deve ser um valor maior ou igual a 1 e menor ou igual a 6!';
    } else {

        if ($tipoUsuarioCadastrar === 3 || $tipoUsuarioCadastrar === 5) {
            $idDaInstituicao = intval(ParametroRequisicao::obterParametro('id_instituicao'));

            // validando se foi informado o id da instituição
            if (empty($idDaInstituicao)) {
                $errosDadosUsuario['instituicao_id'] = 'Informe o id da instituição a qual o usuário faz parte!';
            }

        }

    }

    if (count($errosDadosUsuario) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, $errosDadosUsuario, false);
        exit;
    }

    $usuario->setNome(mb_strtoupper($usuario->getNome()));
    $usuario->setSobrenome(mb_strtoupper($usuario->getSobrenome()));

    // validando o cpf
    if (!ValidaCpf::validarCPF($usuario->getCpf())) {
        $errosDadosUsuario['cpf'] = 'O cpf informado é inválido!';
    }
    
    // validando o e-mail
    if (!ValidaEmail::validarEmail($usuario->getEmail())) {
        $errosDadosUsuario['email'] = 'O e-mail informado é inválido!';
    } elseif (mb_strlen($usuario->getEmail()) > 255 || mb_strlen($usuario->getEmail()) < 3) {
        $errosDadosUsuario['email'] = 'O e-mail deve possuir no mínimo 3 caracteres e no máximo 255 caracteres!';
    }

    // validando a unidade federativa informada
    if (!ValidaUF::validarUF($endereco->getEstado())) {
        $errosDadosUsuario['uf'] = 'O estado informado é inválido!';
    }

    // validando o cep informado
    if (!ValidaCep::validarCep($endereco->getCep())) {
        $errosDadosUsuario['cep'] = 'O cep informado é inválido!';
    }

    // validando a data de nascimento
    if (!ValidaDataNascimento::validarFormatoDataNascimento($dataNascimentoUsuario)) {
        // o formato da data de nascimento é inválido
        $errosDadosUsuario['data_nascimento'] = 'Data de nascimento inválida!';
    } else {
        $dataNascimento = new DateTime($dataNascimentoUsuario);

        if (ValidaDataNascimento::validarSeDataNascimentoEhPosteriorADataAtual($dataNascimento)) {
            // A data de nascimento é posterior a data atual
            $errosDadosUsuario['data_nascimento'] = 'A data de nascimento não pode ser posterior a data atual!';
        } elseif (ValidaDataNascimento::validarSeDataNascimentoEhMuitoAntiga($dataNascimento)) {
            // a data de nascimento é muito antiga
            $errosDadosUsuario['data_nascimento'] = 'A data de nascimento é muito antiga!';
        } else {
            // a data de nascimento está ok
            $usuario->setDataNascimento($dataNascimento);
        }

    }

    // validando se o nome possui no mínimo 3 caracteres
    if (mb_strlen($usuario->getNome()) < 3) {
        $errosDadosUsuario['nome'] = 'O nome deve possuir no mínimo 3 caracteres!';
    } elseif (mb_strlen($usuario->getNome()) > 255) {
        $errosDadosUsuario['nome'] = 'O nome deve possuir no máximo 255 caracteres!';
    }

    // validando se o sobrenome possui no mínimo 3 caracteres
    if (mb_strlen($usuario->getSobrenome()) < 3) {
        $errosDadosUsuario['nome'] = 'O sobrenome deve possuir no mínimo 3 caracteres!';
    } elseif (mb_strlen($usuario->getSobrenome()) > 255) {
        $errosDadosUsuario['sobrenome'] = 'O sobrenome deve possuir no máximo 255 caracteres!';
    }

    // validando a senha
    if ((mb_strlen($usuario->getSenha()) < 6) || (mb_strlen($usuario->getSenha()) > 25)) {
        $errosDadosUsuario['senha'] = 'A senha deve possuir no mínimo 6 caracteres e no máximo 25 caracteres!';
    } elseif  ($usuario->getSenha() != $senhaConfirmacao) {
        $errosDadosUsuario['senha_confirmacao'] = 'A senha e a senha de confirmação devem ser iguais!';
    } else {
        $usuario->setSenha(md5($usuario->getSenha()));
        $senhaConfirmacao = md5($senhaConfirmacao);
    }

    // validando o número de residência
    if ($usuario->getEndereco()->getNumero() === '' || $usuario->getEndereco()->getNumero() === 'S/N') {
        $usuario->getEndereco()->setNumero('s/n');
    } else {
        if (!is_numeric($usuario->getEndereco()->getNumero())) {
            $numeroResTodoMinusculo = mb_strtolower($usuario->getEndereco()->getNumero());
    
            if ($numeroResTodoMinusculo != 's/n') {
                $errosDadosUsuario['numero_residencia'] = 'Caso você não possua um número de residência, informe s/n!';
            }

        } else {

            if (mb_strlen($usuario->getEndereco()->getNumero()) > 255) {
                $errosDadosUsuario['numero_residencia'] = 'O número de residência não deve possuir mais de 255 caracteres!';
            } else {
                $usuario->getEndereco()->setNumero(intval($usuario->getEndereco()->getNumero()));
    
                if ($usuario->getEndereco()->getNumero() <= 0) {
                    $errosDadosUsuario['numero_residencia'] = 'O número de residência não deve ser menor ou igual a zero!';
                }

            }
    
        }
    }

    if (count($errosDadosUsuario) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosDadosUsuario, false);
        exit;
    }
    
    $usuarioDAO = new UsuarioDAO($conexaoBancoDados, 'tbl_usuarios');
    $tipoExpecificoUsuarioDAO = null;
    $precisaValidarSeExisteInstituicaoCadastradaComIdInformado = $tipoUsuarioCadastrar === 3 || $tipoUsuarioCadastrar === 5 ? true : false;

    if ($precisaValidarSeExisteInstituicaoCadastradaComIdInformado) {
        $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');

        if (!$instituicaoDAO->buscarPeloId($idDaInstituicao)) {
            RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, [
                'instituicao_id' => 'Não existe uma instituição cadastrada com o id informado!'
            ], false);
            exit;
        }

    }

    if ($tipoUsuarioCadastrar === 1) {
        $tipoExpecificoUsuarioDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');

        // validando se já existe outro cidadão cadastrado com o e-mail informado
        if ($tipoExpecificoUsuarioDAO->buscarCidadaoPeloEmail($usuario->getEmail())) {
            RespostaHttp::resposta('Já existe outro cidadão cadastrado com o e-mail informado, informe outro e-mail!', 200, null, false);
            exit;
        }

        // validando se já existe outro cidadão cadastrado com o cpf informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloCpf($usuario->getCpf())) {
            RespostaHttp::resposta('Já existe outro cidadão cadastrado com o cpf informado, informe outro cpf!', 200, null, false);
            exit;
        }
        
    } elseif ($tipoUsuarioCadastrar === 2) {
        $tipoExpecificoUsuarioDAO = new GestorSecretariaDAO($conexaoBancoDados, 'tbl_gestores_secretaria');

        // validando se já existe algum gestor de secretaria cadastrado com o e-mail informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloEmail($usuario->getEmail())) {
            RespostaHttp::resposta('Já existe outro gestor de secretaria cadastrado com o e-mail informado, informe outro e-mail!', 200, null, false);
            exit;
        }

        // validando se já existe outro gestor de secretaria cadastrado com o cpf informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloCpf($usuario->getCpf())) {
            RespostaHttp::resposta('Já existe outro gestor de secretaria cadastrado com esse cpf, informe outro cpf!', 200, null, false);
            exit;
        }

    } elseif ($tipoUsuarioCadastrar === 3) {
        $tipoExpecificoUsuarioDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');

        // validando se já existe algum gestor de instituição cadastrado com o e-mail informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloEmail($usuario->getEmail())) {
            RespostaHttp::resposta('Já existe outro gestor de instituição cadastrado com o e-mail informado, informe outro e-mail!', 200, null, false);
            exit;
        }

        // validando se já existe outro gestor de instituição cadastrado com o cpf informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloCpf($usuario->getCpf())) {
            RespostaHttp::resposta('Já existe outro gestor de instituição cadastrado com esse cpf, informe outro cpf!', 200, null, false);
            exit;
        }

    } elseif ($tipoUsuarioCadastrar === 4) {    
        $tipoExpecificoUsuarioDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');

        // validando se já existe algum perito cadastrado com o e-mail informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloEmail($usuario->getEmail())) {
            RespostaHttp::resposta('Já existe outro perito cadastrado com o e-mail informado, informe outro e-mail!', 200, null, false);
            exit;
        }

        // validando se já existe outro gestor de secretaria cadastrado com o cpf informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloCpf($usuario->getCpf())) {
            RespostaHttp::resposta('Já existe outro perito cadastrado com esse cpf, informe outro cpf!', 200, null, false);
            exit;
        }

    } elseif ($tipoUsuarioCadastrar === 5) {
        $tipoExpecificoUsuarioDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');

        // validando se já existe algum tecnico cadastrado com o e-mail informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloEmail($usuario->getEmail())) {
            RespostaHttp::resposta('Já existe outro tecnico cadastrado com o e-mail informado, informe outro e-mail!', 200, null, false);
            exit;
        }

        // validando se já existe outro tecnico cadastrado com o cpf informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloCpf($usuario->getCpf())) {
            RespostaHttp::resposta('Já existe outro tecnico cadastrado com esse cpf, informe outro cpf!', 200, null, false);
            exit;
        }

    } else {
        $tipoExpecificoUsuarioDAO = new SecretarioDAO($conexaoBancoDados, 'tbl_secretarios');

        // validando se já existe algum secretário(a) cadastrado com o e-mail informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloEmail($usuario->getEmail())) {
            RespostaHttp::resposta('Já existe outro secretário cadastrado com o e-mail informado, informe outro e-mail!', 200, null, false);
            exit;
        }

        // validando se já existe outro secretário(a) cadastrado com o cpf informado
        if ($tipoExpecificoUsuarioDAO->buscarPeloCpf($usuario->getCpf())) {
            RespostaHttp::resposta('Já existe outro secretário cadastrado com esse cpf, informe outro cpf!', 200, null, false);
            exit;
        }

    }
    
    $dadosUsuarioCadastrar = [
        'nome' => [ 'dado' => $usuario->getNome(), 'tipo_dado' => PDO::PARAM_STR ],
        'sobrenome' => [ 'dado' => $usuario->getSobrenome(), 'tipo_dado' => PDO::PARAM_STR ],
        'email' => [ 'dado' => $usuario->getEmail(), 'tipo_dado' => PDO::PARAM_STR ],
        'cpf' => [ 'dado' => $usuario->getCpf(), 'tipo_dado' => PDO::PARAM_STR ],
        'telefone' => [ 'dado' => $usuario->getTelefone(), 'tipo_dado' => PDO::PARAM_STR ],
        'sexo' => [ 'dado' => $usuario->getSexo(), 'tipo_dado' => PDO::PARAM_STR ],
        'data_nascimento' => [ 'dado' => $usuario->getDataNascimento()->format('d-m-Y'), 'tipo_dado' => PDO::PARAM_STR ],
        'senha' => [ 'dado' => $usuario->getSenha(), 'tipo_dado' => PDO::PARAM_STR ],
        'status' => [ 'dado' => $usuario->getStatus(), 'tipo_dado' => PDO::PARAM_BOOL ],
        'cep' => [ 'dado' => $usuario->getEndereco()->getCep(), 'tipo_dado' => PDO::PARAM_STR ],
        'logradouro' => [ 'dado' => $usuario->getEndereco()->getLogradouro(), 'tipo_dado' => PDO::PARAM_STR ],
        'complemento' => [ 'dado' => $usuario->getEndereco()->getComplemento(), 'tipo_dado' => PDO::PARAM_STR ],
        'bairro' => [ 'dado' => $usuario->getEndereco()->getBairro(), 'tipo_dado' => PDO::PARAM_STR ],
        'cidade' => [ 'dado' => $usuario->getEndereco()->getCidade(), 'tipo_dado' => PDO::PARAM_STR ],
        'estado' => [ 'dado' => $usuario->getEndereco()->getEstado(), 'tipo_dado' => PDO::PARAM_STR ],
        'numero_residencia' => [ 'dado' => $usuario->getEndereco()->getNumero(), 'tipo_dado' => PDO::PARAM_STR ]
    ];

    // cadastrando os dados do usuário em questão na tabela tbl_usuarios
    if ($usuarioDAO->salvar($dadosUsuarioCadastrar)) {
        $idUsuarioCadastrado = $conexaoBancoDados->lastInsertId();
        $dadosSubUsuarioCadastrar = [];

        if ($tipoUsuarioCadastrar === 3 || $tipoUsuarioCadastrar === 5) {
            $dadosSubUsuarioCadastrar = [
                'usuario_id' => [ 'dado' => $idUsuarioCadastrado, 'tipo_dado' => PDO::PARAM_INT ],
                'instituicao_id' => [ 'dado' => $idDaInstituicao, 'tipo_dado' => PDO::PARAM_INT ]
            ];
        } else {
            $dadosSubUsuarioCadastrar = [
                'usuario_id' => [ 'dado' => $idUsuarioCadastrado, 'tipo_dado' => PDO::PARAM_INT ]
            ];
        }

        if ($tipoExpecificoUsuarioDAO->salvar($dadosSubUsuarioCadastrar)) {
            $conexaoBancoDados->commit();
            RespostaHttp::resposta('Usuário cadastrado com sucesso!', 200, [
                'id' => intval($idUsuarioCadastrado),
                'nome' => $usuario->getNome(),
                'email' => $usuario->getEmail(),
                'telefone' => $usuario->getTelefone(),
                'cpf' => $usuario->getCpf(),
                'sobrenome' => $usuario->getSobrenome(),
                'sexo' => $usuario->getSexo(),
                'data_nascimento' => $usuario->getDataNascimento()->format('d-m-Y'),
                'status' => true
            ], true);
        } else {
            // realizando o rollback da transação
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o usuário em questão!', 200, null, false);
        }
        
    } else {
        // realizando o rollback da transação
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar esse usuário no banco de dados!', 200, null, false);
    }

} catch (AuthException $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    // realizando o rollback da transação
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar esse usuário no banco de dados!', $e->getMessage());
}