<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class EquipeDAO extends DAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }
    
    public function buscarEquipePeloNome($nome) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE nome = :nome;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}