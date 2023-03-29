<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class CidadaoDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarCidadaoPeloCpf($cpf) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE cpf = :cpf;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':cpf', $cpf);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function buscarCidadaoPeloEmail($email) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE email = :email;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}