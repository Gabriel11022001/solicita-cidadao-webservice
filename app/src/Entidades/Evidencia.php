<?php

namespace SistemaSolicitacaoServico\App\Entidades;

class Evidencia
{
    private $id;
    private $descricao;
    private $urlFotoEvidencia;
    private $solicitacaoServicoId;
    private $dataRegistro;

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

    public function setUrlFotoEvidencia($urlFotoEvidencia) {
        $this->urlFotoEvidencia = $urlFotoEvidencia;
    }

    public function getUrlFotoEvidencia() {

        return $this->urlFotoEvidencia;
    }

    public function setSolicitacaoServicoId($solicitacaoServicoId) {
        $this->solicitacaoServicoId = $solicitacaoServicoId;
    }

    public function getSolicitacaoServicoId() {

        return $this->solicitacaoServicoId;
    }

    public function setDataRegistro($dataRegistro) {
        $this->dataRegistro = $dataRegistro;
    }

    public function getDataRegistro() {

        return $this->dataRegistro;
    }
}