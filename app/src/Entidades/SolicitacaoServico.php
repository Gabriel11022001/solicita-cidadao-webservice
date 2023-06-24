<?php

namespace SistemaSolicitacaoServico\App\Entidades;

class SolicitacaoServico
{
    private $id;
    private $titulo;
    private $descricao;
    private $status;
    private $prioridade;
    private $endereco;
    private $urlFoto;
    private $cidadaoId;
    private $instituicaoId;
    private $peritoId;
    private $equipeId;
    private $dataRegistro;
    private $dataLimiteParaTratamento;
    private $posicaoFilaAtendimento;
    private $notaAvaliativa;
    private $observacaoGestorSecretaria;
    private $observacaoSecretario;
    private $observacaoGestorInstituicao;
    private $numeroProtocolo;

    /**
     * @return mixed
     */
    public function getId() {

        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitulo() {

        return $this->titulo;
    }

    /**
     * @param mixed $titulo
     */
    public function setTitulo($titulo) {
        $this->titulo = $titulo;
    }

    /**
     * @return mixed
     */
    public function getDescricao() {

        return $this->descricao;
    }

    /**
     * @param mixed $descricao
     */
    public function setDescricao($descricao) {
        $this->descricao = $descricao;
    }

    /**
     * @return mixed
     */
    public function getStatus() {

        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getPrioridade() {

        return $this->prioridade;
    }

    /**
     * @param mixed $prioridade
     */
    public function setPrioridade($prioridade) {
        $this->prioridade = $prioridade;
    }

    /**
     * @return mixed
     */
    public function getEndereco() {

        return $this->endereco;
    }

    /**
     * @param mixed $endereco
     */
    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    /**
     * @return mixed
     */
    public function getUrlFoto() {

        return $this->urlFoto;
    }

    /**
     * @param mixed $urlFoto
     */
    public function setUrlFoto($urlFoto) {
        $this->urlFoto = $urlFoto;
    }

    /**
     * @return mixed
     */
    public function getCidadaoId() {

        return $this->cidadaoId;
    }

    /**
     * @param mixed $cidadaoId
     */
    public function setCidadaoId($cidadaoId) {
        $this->cidadaoId = $cidadaoId;
    }

    /**
     * @return mixed
     */
    public function getInstituicaoId() {

        return $this->instituicaoId;
    }

    /**
     * @param mixed $instituicaoId
     */
    public function setInstituicaoId($instituicaoId) {
        $this->instituicaoId = $instituicaoId;
    }

    /**
     * @return mixed
     */
    public function getPeritoId() {

        return $this->peritoId;
    }

    /**
     * @param mixed $peritoId
     */
    public function setPeritoId($peritoId) {
        $this->peritoId = $peritoId;
    }

    /**
     * @return mixed
     */
    public function getEquipeId() {

        return $this->equipeId;
    }

    /**
     * @param mixed $equipeId
     */
    public function setEquipeId($equipeId) {
        $this->equipeId = $equipeId;
    }

    /**
     * @return mixed
     */
    public function getDataRegistro() {

        return $this->dataRegistro;
    }

    /**
     * @param mixed $dataRegistro
     */
    public function setDataRegistro($dataRegistro) {
        $this->dataRegistro = $dataRegistro;
    }

    /**
     * @return mixed
     */
    public function getDataLimiteParaTratamento() {

        return $this->dataLimiteParaTratamento;
    }

    /**
     * @param mixed $dataLimiteParaTratamento
     */
    public function setDataLimiteParaTratamento($dataLimiteParaTratamento) {
        $this->dataLimiteParaTratamento = $dataLimiteParaTratamento;
    }

    /**
     * @return mixed
     */
    public function getPosicaoFilaAtendimento() {

        return $this->posicaoFilaAtendimento;
    }

    /**
     * @param mixed $posicaoFilaAtendimento
     */
    public function setPosicaoFilaAtendimento($posicaoFilaAtendimento) {
        $this->posicaoFilaAtendimento = $posicaoFilaAtendimento;
    }

    /**
     * @return mixed
     */
    public function getNotaAvaliativa() {

        return $this->notaAvaliativa;
    }

    /**
     * @param mixed $notaAvaliativa
     */
    public function setNotaAvaliativa($notaAvaliativa) {
        $this->notaAvaliativa = $notaAvaliativa;
    }

    /**
     * @return mixed
     */
    public function getObservacaoGestorSecretaria() {

        return $this->observacaoGestorSecretaria;
    }

    /**
     * @param mixed $observacaoGestorSecretaria
     */
    public function setObservacaoGestorSecretaria($observacaoGestorSecretaria) {
        $this->observacaoGestorSecretaria = $observacaoGestorSecretaria;
    }

    /**
     * @return mixed
     */
    public function getObservacaoSecretario() {

        return $this->observacaoSecretario;
    }

    /**
     * @param mixed $observacaoSecretario
     */
    public function setObservacaoSecretario($observacaoSecretario) {
        $this->observacaoSecretario = $observacaoSecretario;
    }

    /**
     * @return mixed
     */
    public function getObservacaoGestorInstituicao() {

        return $this->observacaoGestorInstituicao;
    }

    /**
     * @param mixed $observacaoGestorInstituicao
     */
    public function setObservacaoGestorInstituicao($observacaoGestorInstituicao) {
        $this->observacaoGestorInstituicao = $observacaoGestorInstituicao;
    }

    /**
     * @return mixed
     */
    public function getNumeroProtocolo() {

        return $this->numeroProtocolo;
    }

    /**
     * @param mixed $numeroProtocolo
     */
    public function setNumeroProtocolo($numeroProtocolo) {
        $this->numeroProtocolo = $numeroProtocolo;
    }
}