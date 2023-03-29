<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

abstract class UsuarioDAO extends DAO    
{
    
    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarPeloCpfSenha($cpf, $senha) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE cpf = :cpf AND senha = :senha;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
        $stmt->bindValue(':senha', $senha, PDO::PARAM_STR);
        $stmt->execute();
        $entidade = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($entidade === false) {

            return [];
        }

        return $entidade;
    }
}