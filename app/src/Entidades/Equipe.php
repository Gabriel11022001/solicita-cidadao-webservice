<?php

namespace SistemaSolicitacaoServico\App\Entidades;

class Equipe
{
    private $id;
    private $nome;
    private $descricao;

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
        $this->descricao = $descricao;
    }

    public function getDescricao() {

        return $this->descricao;
    }
}