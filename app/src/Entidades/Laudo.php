<?php

namespace SistemaSolicitacaoServico\App\Entidades;

class Laudo
{
    private $id;
    private $descricao;
    private $solicitacaoPodeSerTratada;
    private $solicitacaoServicoId;
    private $dataCadastro;

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {

        return $this->id;
    }

    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    public function getDescricao() {

        return $this->descricao;
    }

    public function setDataCadastro($dataCadastro) {
        $this->dataCadastro = $dataCadastro;
    }

    public function getDataCadastro() {

        return $this->dataCadastro;
    }

    public function setSolicitacaoPodeSerTratada($solicitacaoPodeSerTratada) {
        $this->solicitacaoPodeSerTratada = $solicitacaoPodeSerTratada;
    }

    public function getSolicitacaoPodeSerTratada() {

        return $this->solicitacaoPodeSerTratada;
    }

    public function setSolicitacaoServicoId($solicitacaoServicoId) {
        $this->solicitacaoServicoId = $solicitacaoServicoId;
    }

    public function getSolicitacaoServicoId() {

        return $this->solicitacaoServicoId;
    }
}