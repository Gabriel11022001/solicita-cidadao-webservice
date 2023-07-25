<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class CidadaoDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }
    
    public function buscarCidadaoPeloEmail($email) {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS c ON u.id = c.usuario_id AND u.email = :email;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarTodosCidadaos() {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN tbl_cidadaos AS c
        ON u.id = c.usuario_id ORDER BY nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPeloId($id) {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS c
        ON u.id = c.usuario_id AND c.id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarDadosCidadaoPeloIdSolicitacao($idSolicitacao) {
        $query = 'SELECT tblu.id, tblu.nome, tblu.cpf, tblu.email FROM tbl_usuarios AS tblu,
        tbl_cidadaos AS tblc, tbl_solicitacoes_servico AS tbls
        WHERE tblu.id = tblc.usuario_id
        AND tblc.id = tbls.cidadao_id
        AND tbls.id = :sol_id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':sol_id', $idSolicitacao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obterIdCidadaoPeloIdUsuario($idUsuario) {
        $query = 'SELECT tblc.id FROM tbl_cidadaos AS tblc,
        tbl_usuarios AS tblu WHERE tblu.id = tblc.usuario_id
        AND tblu.id = :id_usuario;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}