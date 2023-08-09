<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Entidades\Endereco;
use SistemaSolicitacaoServico\App\Entidades\Instituicao;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCep;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCnpj;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaTelefone;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaUF;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();
    // objeto representando o endereço da instituição
    $endereco = new Endereco();
    $endereco->setLogradouro(trim(ParametroRequisicao::obterParametro('logradouro')));
    $endereco->setComplemento(trim(ParametroRequisicao::obterParametro('complemento')));
    $endereco->setNumero(trim(ParametroRequisicao::obterParametro('numero')));
    $endereco->setBairro(trim(ParametroRequisicao::obterParametro('bairro')));
    $endereco->setEstado(trim(ParametroRequisicao::obterParametro('estado')));
    $endereco->setCep(trim(ParametroRequisicao::obterParametro('cep')));
    $endereco->setCidade(trim(ParametroRequisicao::obterParametro('cidade')));
    // objeto representando a instituição
    $instituicao = new Instituicao();
    $instituicao->setNome(mb_strtoupper(trim(ParametroRequisicao::obterParametro('nome'))));
    $instituicao->setEmail(trim(ParametroRequisicao::obterParametro('email')));
    $instituicao->setTelefone(trim(ParametroRequisicao::obterParametro('telefone')));
    $instituicao->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $instituicao->setObservacao(trim(ParametroRequisicao::obterParametro('observacao')));
    $instituicao->setCnpj(trim(ParametroRequisicao::obterParametro('cnpj')));
    $instituicao->setEndereco($endereco);
    $instituicao->setDataCadastro(new DateTime());
    $errosFormulario = ValidaCamposObrigatorios::validarFormularioCadastrarInstituicao($instituicao);

    if (count($errosFormulario) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosFormulario, false);
        exit;
    }

    // validando se o cep do endereço da instituição é válido
    if (!ValidaCep::validarCep($instituicao->getEndereco()->getCep())) {
        $errosFormulario['cep'] = 'O cep informado é inválido!';
    }

    // validando se o cnpj informado é válido
    if (!ValidaCnpj::validarCnpj($instituicao->getCnpj())) {
        $errosFormulario['cnpj'] = 'O cnpj informado é inválido!';
    }

    // validando o e-mail da instituição
    if (!ValidaEmail::validarEmail($instituicao->getEmail())) {
        $errosFormulario['email'] = 'O e-mail informado é inválido!';
    } elseif (strlen($instituicao->getEmail()) > 255 || strlen($instituicao->getEmail()) < 3) {
        $errosFormulario['email'] = 'O e-mail da instituição deve possuir no máximo 255 caracteres e no mínimo 3 caracteres!';
    }

    // validando se o nome da instituição possui pelo menos 3 caracteres
    if (strlen($instituicao->getNome()) < 3) {
        $errosFormulario['nome'] = 'O nome da instituição deve possuir no mínimo 3 caracteres!';
    }

    // verificando se o logradouro possui pele menos 3 caracteres
    if (strlen($endereco->getLogradouro()) < 3) {
        $errosFormulario['logradouro'] = 'O logradouro deve possuir no mínimo 3 caracteres!';
    }

    // verificando se o bairro possui pelo menos 3 caracteres
    if (strlen($endereco->getBairro()) < 3) {
        $errosFormulario['bairro'] = 'O bairro deve possuir no mínimo 3 caracteres!';
    }

    // verificando se a cidade possui pelo menos 3 caracteres
    if (strlen($endereco->getCidade()) < 3) {
        $errosFormulario['cidade'] = 'A cidade deve possuir no mínimo 3 caracteres!';
    }

    // validando o estado(unidade federativa) informado
    $resValidarUF = ValidaUF::validarUF($endereco->getEstado());
    if ($resValidarUF != 'ok') {
        $errosFormulario['estado'] = $resValidarUF;
    }

    // validando o número
    if ($instituicao->getEndereco()->getNumero() === '' || $instituicao->getEndereco()->getNumero() === 'S/N') {
        $instituicao->getEndereco()->setNumero('s/n');
    } else {
        if (!is_numeric($instituicao->getEndereco()->getNumero())) {
            $numeroResTodoMinusculo = mb_strtolower($instituicao->getEndereco()->getNumero());
    
            if ($numeroResTodoMinusculo != 's/n') {
                $errosFormulario['numero_residencia'] = 'Caso a instituição não possua um número de residência, informe s/n!';
            }

        } else {

            if (mb_strlen($instituicao->getEndereco()->getNumero()) > 255) {
                $errosFormulario['numero_residencia'] = 'O número de residência não deve possuir mais de 255 caracteres!';
            } else {
                $instituicao->getEndereco()->setNumero(intval($instituicao->getEndereco()->getNumero()));
    
                if ($instituicao->getEndereco()->getNumero() <= 0) {
                    $errosFormulario['numero_residencia'] = 'O número de residência não deve ser menor ou igual a zero!';
                }

            }
    
        }
    }

    // validando o telefone
    if (!ValidaTelefone::validarTelefone($instituicao->getTelefone())) {
        $errosFormulario['telefone'] = 'O telefone informado é inválido, o formato do telefone deve ser (00) 00000-0000 ou (00) 0000-0000!';
    }

    if (count($errosFormulario) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosFormulario, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');

    // verificando se já existe outra instituição cadastrada com o mesmo nome
    if ($instituicaoDAO->buscarInstituicaoPeloNome($instituicao->getNome())) {
        RespostaHttp::resposta('Já existe uma instituição cadastrada com o nome informado!', 200, null, false);
        exit;
    }

    // validando se já existe outra instituição cadastrada com o cnpj informado
    if ($instituicaoDAO->buscarInstituicaoPeloCnpj($instituicao->getCnpj())) {
        RespostaHttp::resposta('Já existe uma instituição cadastrada com o cnpj informado!', 200, null, false);
        exit;
    }

    $dadosInstituicaoCadastrar = [
        'nome' => [
            'dado' => $instituicao->getNome(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'descricao' => [
            'dado' => $instituicao->getDescricao(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'observacao' => [
            'dado' => $instituicao->getObservacao(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cnpj' => [
            'dado' => $instituicao->getCnpj(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'data_cadastro' => [
            'dado' => $instituicao->getDataCadastro()->format('Y-m-d H:i:s'),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'logradouro' => [
            'dado' => $instituicao->getEndereco()->getLogradouro(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'complemento' => [
            'dado' => $instituicao->getEndereco()->getComplemento(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'bairro' => [
            'dado' => $instituicao->getEndereco()->getBairro(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'estado' => [
            'dado' => $instituicao->getEndereco()->getEstado(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cidade' => [
            'dado' => $instituicao->getEndereco()->getCidade(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'numero' => [
            'dado' => $instituicao->getEndereco()->getNumero(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'cep' => [
            'dado' => $instituicao->getEndereco()->getCep(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'email' => [
            'dado' => $instituicao->getEmail(),
            'tipo_dado' => PDO::PARAM_STR
        ],
        'telefone' => [
            'dado' => $instituicao->getTelefone(),
            'tipo_dado' => PDO::PARAM_STR
        ]
    ];

    if ($instituicaoDAO->salvar($dadosInstituicaoCadastrar)) {
        RespostaHttp::resposta('Instituição cadastrada com sucesso!', 201, [
            'id' => intval($conexaoBancoDados->lastInsertId()),
            'nome' => $instituicao->getNome(),
            'descricao' => $instituicao->getDescricao(),
            'email' => $instituicao->getEmail(),
            'telefone' => $instituicao->getTelefone(),
            'observacao' => $instituicao->getObservacao(),
            'data_cadastro' => $instituicao->getDataCadastro()->format('d-m-Y H:i:s'),
            'status' => true,
            'cnpj' => $instituicao->getCnpj(),
            'logradouro' => $endereco->getLogradouro(),
            'complemento' => $endereco->getComplemento(),
            'bairro' => $endereco->getBairro(),
            'cidade' => $endereco->getCidade(),
            'cep' => $endereco->getCep(),
            'estado' => $endereco->getEstado(),
            'numero' => $endereco->getNumero()
        ]);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se cadastrar a instituição!', 200, null, false);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se cadastrar a instituição!', $e->getMessage());
}