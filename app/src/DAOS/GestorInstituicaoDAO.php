<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class GestorInstituicaoDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarGestoresInstituicaoPeloIdDaInstituicao($idInstituicao) {
        $query = 'SELECT u.nome, u.cpf FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS gi
        ON u.id = gi.usuario_id AND gi.instituicao_id = :instituicao_id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':instituicao_id', $idInstituicao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarTodosGestoresInstituicao() {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS gi
        ON u.id = gi.usuario_id ORDER BY nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarEmailsGestoresInstituicao($idInstituicao) {
        $query = 'SELECT tblu.email FROM tbl_usuarios AS tblu,
        tbl_gestores_instituicao AS tblgi, tbl_instituicoes AS tbli
        WHERE tblu.id = tblgi.usuario_id
        AND tblgi.instituicao_id = tbli.id
        AND tbli.id = :instituicao_id
        AND tblu.status = true;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':instituicao_id', $idInstituicao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}