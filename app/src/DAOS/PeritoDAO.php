<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class PeritoDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarTodosPeritos() {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS p
        ON u.id = p.usuario_id ORDER BY nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarTodosPeritosAtivos() {
        $query = 'SELECT tblp.id, tblu.nome, tblu.cpf, tblp.usuario_id
        FROM tbl_usuarios AS tblu INNER JOIN tbl_peritos AS tblp
        ON tblu.status = true AND tblu.id = tblp.usuario_id
        ORDER BY tblu.nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterIdPeritoPeloIdUsuario($idUsuario) {
        $query = 'SELECT id FROM ' . $this->nomeTabela . ' WHERE usuario_id = :usuario_id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':usuario_id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}