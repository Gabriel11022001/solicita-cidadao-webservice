<?php

namespace SistemaSolicitacaoServico\App\Entidades;

class Usuario
{
    private $id;
    private $nome;
    private $sobrenome;
    private $sexo;
    private $cpf;
    private $email;
    private $telefone;
    private $dataNascimento;
    private $senha;
    private $status;
    private $endereco;

    public function getId() {

        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNome() {

        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getSobrenome() {

        return $this->sobrenome;
    }

    public function setSobrenome($sobrenome) {
        $this->sobrenome = $sobrenome;
    }

    public function getSexo() {

        return $this->sexo;
    }

    public function setSexo($sexo) {
        $this->sexo = $sexo;
    }

    public function getCpf() {

        return $this->cpf;
    }

    public function setCpf($cpf) {
        $this->cpf = $cpf;
    }

    public function getEmail() {

        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getTelefone() {

        return $this->telefone;
    }

    public function setTelefone($telefone) {
        $this->telefone = $telefone;
    }

    public function getDataNascimento() {

        return $this->dataNascimento;
    }

    public function setDataNascimento($dataNascimento) {
        $this->dataNascimento = $dataNascimento;
    }

    public function getSenha() {

        return $this->senha;
    }

    public function setSenha($senha) {

        $this->senha = $senha;
    }

    public function getStatus() {

        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getEndereco() {
        
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }
}