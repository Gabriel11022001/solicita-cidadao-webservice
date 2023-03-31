<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class CidadaoDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarCidadaoPeloEmail($email) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE email = :email;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}