<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class SolicitacaoServicoDAO extends DAO
{
    
    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarTodasSolicitacoesServicoCidadao($idCidadao) {
        $query = 'SELECT tblsc.id, tblsc.titulo, tblsc.posicao_fila,
        tblsc.protocolo, tblsc.prioridade, tblsc.data_registro,
        tblsc.status FROM tbl_solicitacoes_servico AS tblsc, tbl_usuarios AS tblu,
        tbl_cidadaos AS tblc WHERE tblsc.cidadao_id = tblc.id AND
        tblu.id = tblc.usuario_id AND tblc.id = :cidadao_id
        ORDER BY tblsc.posicao_fila ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':cidadao_id', $idCidadao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function alterarPrioridadeDaSolicitacaoServico($id, $novaPrioridade) {

    }

    public function alterarPosicaoSolicitacaoNaFilaDeAtendimento($id, $novaPosicao) {
        $query = 'UPDATE ' . $this->nomeTabela . ' SET posicao_fila = :nova_posicao_fila
        WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nova_posicao_fila', $novaPosicao, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarTodasSolicitacoesComPrioridadeAlta() {
        $query = "SELECT id, posicao_fila FROM " . $this->nomeTabela . " WHERE prioridade = 'Alta'
        ORDER BY posicao_fila ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarTodasSolicitacoesComPrioridadeNormal() {
        $query = "SELECT id, posicao_fila FROM " . $this->nomeTabela . " WHERE prioridade = 'Normal'
        ORDER BY posicao_fila ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarTodasSolicitacoesComPrioridadeBaixa() {
        $query = "SELECT id, posicao_fila FROM " . $this->nomeTabela . " WHERE prioridade = 'Baixa'
        ORDER BY posicao_fila ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function alterarNumeroProtocoloSolicitacaoServico($id, $numeroProtocolo) {
        $query = 'UPDATE ' . $this->nomeTabela . ' SET protocolo = :numero_protocolo
        WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':numero_protocolo', $numeroProtocolo, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarTodasSolicitacoesServico() {
        $query = "SELECT tbls.id, tbls.titulo, tbls.protocolo,
        tbls.prioridade, tbls.posicao_fila, tbls.status, tbls.data_registro,
        tblu.nome AS nome_cidadao, tblu.cpf AS cpf_cidadao
        FROM tbl_solicitacoes_servico AS tbls, tbl_cidadaos
        AS tblc, tbl_usuarios AS tblu WHERE tbls.cidadao_id = tblc.id
        AND tblc.usuario_id = tblu.id
        AND tbls.status <> 'Concluído' 
        AND tbls.status <> 'Cancelado'
        AND tbls.status <> 'Reprovado pelo perito'
        ORDER BY tbls.posicao_fila ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarSolicitacoesServicoInstituicaoParaEncaminharParaEquipe($idInstituicao) {
        $query = "SELECT id, titulo, protocolo, posicao_fila,
        status, prioridade, data_registro
        FROM tbl_solicitacoes_servico WHERE status = 'Aguardando encaminhamento a equipe responsável'
        AND instituicao_id = :instituicao_id
        ORDER BY posicao_fila ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':instituicao_id', $idInstituicao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarSolicitacaoPeloId($id) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cancelarSolicitacao($id) {
        $query = "UPDATE " . $this->nomeTabela . " SET status = 'Cancelado', posicao_fila = -1
        WHERE id = :id;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function buscarSolicitacoesServicoPerito($peritoId) {
        $query = "SELECT tbls.id, tbls.titulo, tbls.protocolo, tbls.posicao_fila,
        tbls.status, tbls.prioridade, tbls.data_registro FROM tbl_solicitacoes_servico AS tbls,
        tbl_peritos AS tblp  WHERE tbls.perito_id = tblp.id
        AND tblp.id = :perito_id
        AND tbls.status = 'Aguardando análise do perito'
        ORDER BY tbls.posicao_fila ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':perito_id', $peritoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarSolicitacoesEquipe($equipeId) {
        $query = "SELECT id, titulo, protocolo, posicao_fila,
        status, prioridade, data_registro FROM tbl_solicitacoes_servico 
        WHERE equipe_id = :equipe_id
        AND status = 'Aguardando tratamento'
        ORDER BY posicao_fila ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':equipe_id', $equipeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function encaminharSolicitacaoParaInstituicaoOuPerito(
        $idSolicitacao,
        $idInstituicao,
        $idPerito,
        $novoStatusSolicitacao,
        $prioridade,
        $observacaoSecretario,
        $observacaoGestorSecretaria
    ) {
        $query = 'UPDATE ' . $this->nomeTabela . ' SET status = :novo_status_solicitacao,
        prioridade = :prioridade, ';

        if (empty($idPerito)) {
            $query .= 'instituicao_id = :instituicao_id, ';
        } else {
            $query .= 'perito_id = :perito_id, ';
        }

        if (empty($observacaoSecretario)) {
            $query .= 'observacao_gestor_secretaria = :obs_gestor_secretaria ';
        } else {
            $query .= 'observacao_secretario = :obs_secretario ';
        }

        $query .= ' WHERE id = :solicitacao_servico_id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':solicitacao_servico_id', $idSolicitacao, PDO::PARAM_INT);
        $stmt->bindValue(':novo_status_solicitacao', $novoStatusSolicitacao, PDO::PARAM_STR);
        $stmt->bindValue(':prioridade', $prioridade, PDO::PARAM_STR);

        if (empty($idPerito)) {
            $stmt->bindValue(':instituicao_id', $idInstituicao, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':perito_id', $idPerito, PDO::PARAM_INT);
        }

        if (empty($observacaoSecretario)) {
            $stmt->bindValue(':obs_gestor_secretaria', $observacaoGestorSecretaria, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':obs_secretario', $observacaoSecretario, PDO::PARAM_STR);
        }

        return $stmt->execute();
    }
}