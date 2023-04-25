<?php

namespace SistemaSolicitacaoServico\App\Entidades;

class Instituicao
{
    private $id;
    private $nome;
    private $descricao;
    private $observacao;
    private $status;
    private $endereco;
    private $dataCadastro;
    private $cnpj;

    public function __construct() {
        $this->observacao = '';
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {

        return $this->id;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getNome() {

        return $this->nome;
    }

    public function setDescricao($descricao) {
        $this->descricao;
    }

    public function getDescricao() {

        return $this->descricao;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {

        return $this->status;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function getEndereco() {

        return $this->endereco;
    }

    public function setDataCadastro($dataCadastro) {
        $this->dataCadastro = $dataCadastro;
    }

    public function getDataCadastro() {

        return $this->dataCadastro;
    }

    public function setObservacao($observacao) {
        $this->observacao = $observacao;
    }

    public function getObservacao() {

        return $this->observacao;
    }

    public function setCnpj($cnpj) {
        $this->cnpj = $cnpj;
    }

    public function getCnpj() {

        return $this->cnpj;
    }
}