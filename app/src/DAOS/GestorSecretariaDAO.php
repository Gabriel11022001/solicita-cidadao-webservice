<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class GestorSecretariaDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarTodosGestoresSecretaria() {
        $query = 'SELECT * FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS gs
        ON u.id = gs.usuario_id ORDER BY nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarEmailsGestoresSecretaria() {
        $query = 'SELECT tblu.email FROM tbl_usuarios AS tblu, tbl_gestores_secretaria AS tblgs
        WHERE tblu.id = tblgs.usuario_id AND tblu.status = true;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}