<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Entidades\Endereco;
use SistemaSolicitacaoServico\App\Entidades\Instituicao;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCep;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCnpj;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaTelefone;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaUF;

try {
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
    $instituicao->setId(intval(ParametroRequisicao::obterParametro('id')));
    $instituicao->setNome(mb_strtoupper(trim(ParametroRequisicao::obterParametro('nome'))));
    $instituicao->setEmail(trim(ParametroRequisicao::obterParametro('email')));
    $instituicao->setTelefone(trim(ParametroRequisicao::obterParametro('telefone')));
    $instituicao->setDescricao(trim(ParametroRequisicao::obterParametro('descricao')));
    $instituicao->setObservacao(trim(ParametroRequisicao::obterParametro('observacao')));
    $instituicao->setCnpj(trim(ParametroRequisicao::obterParametro('cnpj')));
    $instituicao->setStatus(ParametroRequisicao::obterParametro('status'));
    $instituicao->setEndereco($endereco);
    $errosCampos = ValidaCamposObrigatorios::validarFormularioEditarInstituicao($instituicao);

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosCampos, false);
        exit;
    }

    // validando se o cep do endereço da instituição é válido
    if (!ValidaCep::validarCep($instituicao->getEndereco()->getCep())) {
        $errosCampos['cep'] = 'O cep informado é inválido!';
    }

    // validando se o cnpj informado é válido
    if (!ValidaCnpj::validarCnpj($instituicao->getCnpj())) {
        $errosCampos['cnpj'] = 'O cnpj informado é inválido!';
    }

    // validando o e-mail da instituição
    if (!ValidaEmail::validarEmail($instituicao->getEmail())) {
        $errosCampos['email'] = 'O e-mail informado é inválido!';
    }

    // validando se o nome da instituição possui pelo menos 3 caracteres
    if (strlen($instituicao->getNome()) < 3) {
        $errosCampos['nome'] = 'O nome da instituição deve possuir no mínimo 3 caracteres!';
    }

    // verificando se o logradouro possui pele menos 3 caracteres
    if (strlen($endereco->getLogradouro()) < 3) {
        $errosCampos['logradouro'] = 'O logradouro deve possuir no mínimo 3 caracteres!';
    }

    // verificando se o bairro possui pelo menos 3 caracteres
    if (strlen($endereco->getBairro()) < 3) {
        $errosCampos['bairro'] = 'O bairro deve possuir no mínimo 3 caracteres!';
    }

    // verificando se a cidade possui pelo menos 3 caracteres
    if (strlen($endereco->getCidade()) < 3) {
        $errosCampos['cidade'] = 'A cidade deve possuir no mínimo 3 caracteres!';
    }

    // validando o estado(unidade federativa) informado
    $resValidarUF = ValidaUF::validarUF($endereco->getEstado());
    if ($resValidarUF != 'ok') {
        $errosCampos['estado'] = $resValidarUF;
    }

    // validando se o e-mail informado possui até no máximo 255 caracteres e no mínimo 3 caracteres
    if (strlen($instituicao->getEmail()) > 255 || strlen($instituicao->getEmail()) < 3) {
        $errosCampos['email'] = 'O e-mail da instituição deve possuir no máximo 255 caracteres e no mínimo 3 caracteres!';
    }

    // validando o telefone
    if (!ValidaTelefone::validarTelefone($instituicao->getTelefone())) {
        $errosCampos['telefone'] = 'O telefone informado é inválido, o formato do telefone deve ser (00) 00000-0000 ou (00) 0000-0000!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Ocorreram erros de validação de campos!', 200, $errosCampos, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');

    // validando se existe uma instituição cadastrada com o id informado
    if (!$instituicaoDAO->buscarPeloId($instituicao->getId())) {
        RespostaHttp::resposta('Não existe uma instituição cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    // validando se já existe outra instituição cadastrada com o cnpj informado
    $instituicaoQuePossuiCnpjInformado = $instituicaoDAO
        ->buscarInstituicaoPeloCnpj($instituicao->getCnpj());
    
    if ($instituicaoQuePossuiCnpjInformado) {
        // foi encontrado uma instituição com o cnpj informado

        if ($instituicaoQuePossuiCnpjInformado['id'] != $instituicao->getId()) {
            // não pode utilizar esse cnpj pois ele já pertence a outra instituição
            RespostaHttp::resposta('Já existe outra instituição cadastrada com esse cnpj, informe outro cnpj!', 200, null, false);
            exit;
        }

    }
    
    // validando se já existe outra instituição cadastrada com o nome informado
    $instituicaoQuePossuiNomeInformado = $instituicaoDAO->buscarInstituicaoPeloNome($instituicao->getNome());
    
    if ($instituicaoQuePossuiNomeInformado) {
        // foi encontrado uma instituição com o nome informado

        if ($instituicaoQuePossuiNomeInformado['id'] != $instituicao->getId()) {
            RespostaHttp::resposta('Já existe outra instituição cadastrada com esse nome, informe outro nome!');
            exit;
        }

    }

    $novosDadosInstituicao = [
        'id' => [
            'dado' => $instituicao->getId(),
            'tipo_dado' => PDO::PARAM_INT
        ],
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
        ],
        'status' => [
            'dado' => $instituicao->getStatus(),
            'tipo_dado' => PDO::PARAM_BOOL
        ]
    ];

    if ($instituicaoDAO->editarInstituicao($novosDadosInstituicao)) {
        $dadosInstituicaoRetornar = $instituicaoDAO->buscarPeloId($instituicao->getId());
        RespostaHttp::resposta('Os dados da instituição foram alterados com sucesso!', 200, $dadosInstituicaoRetornar, true);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar os dados da instituição!', 200, null, false);
    }

} catch (Exception $e) {
    var_dump($e->getMessage());
}