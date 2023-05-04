<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Entidades\Cidadao;
use SistemaSolicitacaoServico\App\Entidades\Endereco;
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
try {
    $cidadao = new Cidadao();
    $cidadao->setNome(trim(mb_strtoupper(ParametroRequisicao::obterParametro('nome'))));
    $cidadao->setSobrenome(trim(mb_strtoupper(ParametroRequisicao::obterParametro('sobrenome'))));
    $cidadao->setEmail(trim(ParametroRequisicao::obterParametro('email')));
    $cidadao->setTelefone(trim(ParametroRequisicao::obterParametro('telefone')));
    $cidadao->setCpf(trim(ParametroRequisicao::obterParametro('cpf')));
    $cidadao->setSexo(ParametroRequisicao::obterParametro('sexo'));
    $cidadao->setSenha(trim(ParametroRequisicao::obterParametro('senha')));
    $senhaConfirmacao = trim(ParametroRequisicao::obterParametro('senha_confirmacao'));
    $dataNascimento = trim(ParametroRequisicao::obterParametro('data_nascimento'));
    // endereço do cidadão a ser cadastrado
    $enderecoCidadao = new Endereco();
    $enderecoCidadao->setLogradouro(ParametroRequisicao::obterParametro('logradouro'));
    $enderecoCidadao->setComplemento(ParametroRequisicao::obterParametro('complemento'));
    $enderecoCidadao->setNumero(trim(ParametroRequisicao::obterParametro('numero_residencia')));
    $enderecoCidadao->setEstado(trim(ParametroRequisicao::obterParametro('uf')));
    $enderecoCidadao->setCidade(ParametroRequisicao::obterParametro('cidade'));
    $enderecoCidadao->setBairro(ParametroRequisicao::obterParametro('bairro'));
    $enderecoCidadao->setCep(trim(ParametroRequisicao::obterParametro('cep')));
    $cidadao->setEndereco($enderecoCidadao);
    $errosCampos = ValidaCamposObrigatorios::validarFormularioCadastroCidadao(
        $cidadao->getNome(),
        $cidadao->getSobrenome(),
        $cidadao->getEmail(),
        $cidadao->getTelefone(),
        $cidadao->getCpf(),
        $cidadao->getSexo(),
        $dataNascimento,
        $enderecoCidadao->getLogradouro(),
        $enderecoCidadao->getCep(),
        $enderecoCidadao->getBairro(),
        $enderecoCidadao->getCidade(),
        $enderecoCidadao->getEstado(),
        $cidadao->getSenha(),
        $senhaConfirmacao
    );

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Preencha todos os campos obrigatórios!', 200, $errosCampos, false);
        exit;
    }

    // validando o cpf
    if (!ValidaCpf::validarCPF($cidadao->getCpf())) {
        $errosCampos['cpf'] = 'O cpf informado é inválido!';
    }
    
    // validando o e-mail
    if (!ValidaEmail::validarEmail($cidadao->getEmail())) {
        $errosCampos['email'] = 'O e-mail informado é inválido!';
    }

    // validando a unidade federativa informada
    if (!ValidaUF::validarUF($enderecoCidadao->getEstado())) {
        $errosCampos['uf'] = 'O estado informado é inválido!';
    }

    // validando o cep informado
    if (!ValidaCep::validarCep($enderecoCidadao->getCep())) {
        $errosCampos['cep'] = 'O cep informado é inválido!';
    }

    // validando a data de nascimento
    if (!ValidaDataNascimento::validarFormatoDataNascimento($dataNascimento)) {
        // o formato da data de nascimento é inválido
        $errosCampos['data_nascimento'] = 'Data de nascimento inválida!';
    } else {
        $dataNascimento = new DateTime($dataNascimento);

        if (ValidaDataNascimento::validarSeDataNascimentoEhPosteriorADataAtual($dataNascimento)) {
            // A data de nascimento é posterior a data atual
            $errosCampos['data_nascimento'] = 'A data de nascimento não pode ser posterior a data atual!';
        } elseif (ValidaDataNascimento::validarSeDataNascimentoEhMuitoAntiga($dataNascimento)) {
            // a data de nascimento é muito antiga
            $errosCampos['data_nascimento'] = 'A data de nascimento é muito antiga!';
        } else {
            // a data de nascimento está ok
            $cidadao->setDataNascimento($dataNascimento);
        }

    }

    // validando se o nome possui no mínimo 3 caracteres
    if (mb_strlen($cidadao->getNome()) < 3) {
        $errosCampos['nome'] = 'O nome deve possuir no mínimo 3 caracteres!';
    }

    // validando se o sobrenome possui no mínimo 3 caracteres
    if (mb_strlen($cidadao->getSobrenome()) < 3) {
        $errosCampos['nome'] = 'O sobrenome deve possuir no mínimo 3 caracteres!';
    }

    // validando a senha
    if ((mb_strlen($cidadao->getSenha()) < 6) || (mb_strlen($cidadao->getSenha()) > 15)) {
        $errosCampos['senha'] = 'A senha deve possuir no mínimo 6 caracteres e no máximo 15 caracteres!';
    } elseif  ($cidadao->getSenha() != $senhaConfirmacao) {
        $errosCampos['senha_confirmacao'] = 'A senha e a senha de confirmação devem ser iguais!';
    }

    // validando o número de residência
    
    // =========================================================================

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosCampos, false);
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
        'sexo' => [
            'dado' => $cidadao->getSexo(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'data_nascimento' => [
            'dado' => $cidadao->getDataNascimento()->format('d-m-Y'),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'senha' => [
            'dado' => md5($cidadao->getSenha()),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'logradouro' => [
            'dado' => $cidadao->getEndereco()->getLogradouro(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'complemento' => [
            'dado' => $cidadao->getEndereco()->getComplemento(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cidade' => [
            'dado' => $cidadao->getEndereco()->getCidade(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'bairro' => [
            'dado' => $cidadao->getEndereco()->getBairro(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'estado' => [
            'dado' => $cidadao->getEndereco()->getEstado(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'numero_residencia' => [
            'dado' => $cidadao->getEndereco()->getNumero(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cep' => [
            'dado' => $cidadao->getEndereco()->getCep(),
            'tipo_dado' => PDO::PARAM_STR
        ]
    ];
    // iniciando a transação
    $conexaoBancoDados->beginTransaction();

    if ($usuarioDAO->salvar($dadosUsuarioCadastrar)) {
        $usuarioId = intval($conexaoBancoDados->lastInsertId());
        $dadosCidadaoCadastrar = [
            'usuario_id' => [
                'dado' => $usuarioId,
                'tipo_dado' => PDO::PARAM_INT
            ]
        ];

        if ($cidadaoDAO->salvar($dadosCidadaoCadastrar)) {
            // cidadão foi cadastrado com sucesso
            $idCidadaoCadastrado = intval($conexaoBancoDados->lastInsertId());
            // comitando a transação
            $conexaoBancoDados->commit();
            $dadosCidadaoRetornar = [
                'id' => $idCidadaoCadastrado,
                'nome' => $cidadao->getNome(),
                'sobrenome' => $cidadao->getSobrenome(),
                'cpf' => $cidadao->getCpf(),
                'email' => $cidadao->getEmail(),
                'telefone' => $cidadao->getTelefone(),
                'data_nascimento' => $cidadao->getDataNascimento()->format('d-m-Y'),
                'sexo' => $cidadao->getSexo(),
                'status' => true,
                'cep' => $cidadao->getEndereco()->getCep(),
                'logradouro' => $cidadao->getEndereco()->getLogradouro(),
                'complemento' => $cidadao->getEndereco()->getComplemento(),
                'bairro' => $cidadao->getEndereco()->getBairro(),
                'cidade' => $cidadao->getEndereco()->getCidade(),
                'numero_residencia' => $cidadao->getEndereco()->getNumero(),
                'estado' => $cidadao->getEndereco()->getEstado()
            ];
            RespostaHttp::resposta('Cidadão cadastrado com sucesso!', 201, $dadosCidadaoRetornar);
        } else {
            // ocorreu um erro ao tentar-se cadastrar os dados do cidadão na tabela tbl_cidadaos
            $conexaoBancoDados->rollBack();
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão!', 200, null, false);
        }

    } else {
        // ocorreu um erro ao tentar-se cadastrar os dados do usuário na tabela tbl_usuarios
        $conexaoBancoDados->rollBack();
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão!', 200, null, false);
    }

} catch (Exception $e) {
    // realizando o rollback da transação
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar o cidadão!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar o cidadão!', 200, null, false);
}