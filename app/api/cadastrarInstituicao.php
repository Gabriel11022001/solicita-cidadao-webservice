<?php

use SistemaSolicitacaoServico\App\Entidades\Endereco;
use SistemaSolicitacaoServico\App\Entidades\Instituicao;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCamposObrigatorios;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCep;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCnpj;

try {
    $instituicao = new Instituicao();
    $endereco = new Endereco();
    $endereco->setLogradouro(ParametroRequisicao::obterParametro('logradouro'));
    $endereco->setComplemento(ParametroRequisicao::obterParametro('complemento'));
    $endereco->setNumero(ParametroRequisicao::obterParametro('numero'));
    $endereco->setBairro(ParametroRequisicao::obterParametro('bairro'));
    $endereco->setEstado(ParametroRequisicao::obterParametro('estado'));
    $endereco->setCep(ParametroRequisicao::obterParametro('cep'));
    $instituicao->setNome(ParametroRequisicao::obterParametro('nome'));
    $instituicao->setDescricao(ParametroRequisicao::obterParametro('descricao'));
    $instituicao->setObservacao(ParametroRequisicao::obterParametro('observacao'));
    $instituicao->setCnpj(ParametroRequisicao::obterParametro('cnpj'));
    $instituicao->setEndereco($endereco);
    $errosFormulario = ValidaCamposObrigatorios::validarFormularioCadastrarInstituicao($instituicao);

    if (count($errosFormulario) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 400, $errosFormulario);
        exit;
    }

    // validando se o cep do endereço da instituição é válido
    if (!ValidaCep::validarCep($instituicao->getEndereco()->getCep())) {
        RespostaHttp::resposta('O cep informado para o endereço da instituição é inválido!', 400, null);
        exit;
    }

    // validando se o cnpj informado é válido
    if (!ValidaCnpj::validarCnpj($instituicao->getCnpj())) {
        RespostaHttp::resposta('O cnpj informado é inválido!', 400, null);
        exit;
    }

} catch (Exception $e) {

}