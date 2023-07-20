<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class SecretarioDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarTodosSecretarios() {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS s
        ON u.id = s.usuario_id ORDER BY nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarEmailsSecretarios() {
        $query = 'SELECT tblu.email FROM tbl_usuarios AS tblu, tbl_secretarios AS tbls
        WHERE tblu.id = tbls.usuario_id AND tblu.status = true;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}