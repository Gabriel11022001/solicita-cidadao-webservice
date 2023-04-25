<?php

namespace SistemaSolicitacaoServico\App\Entidades;

class Endereco
{
    private $logradouro;
    private $complemento;
    private $bairro;
    private $cidade;
    private $estado;
    private $numero;
    private $cep;

    public function __construct() {
        $this->complemento = '';
        $this->numero = 's/n';
    }

    public function setLogradouro($logradouro) {
        $this->logradouro = $logradouro;
    }

    public function getLogradouro() {

        return $this->logradouro;
    }

    public function setComplemento($complemento) {
        $this->complemento = $complemento;
    }

    public function getComplemento() {

        return $this->complemento;
    }

    public function setBairro($bairro) {
        $this->bairro = $bairro;
    }

    public function getBairro() {
        
        return $this->bairro;
    }

    public function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    public function getCidade() {

        return $this->cidade;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function getEstado() {

        return $this->estado;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function getNumero() {

        return $this->numero;
    }

    public function setCep($cep) {
        $this->cep = $cep;
    }

    public function getCep() {

        return $this->cep;
    }
}