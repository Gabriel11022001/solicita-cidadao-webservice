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

    public function buscarEquipeComFiltroDeTexto($filtroTexto) {
        // consultar equipe pelo nome mas com filtragem
        $query = "SELECT * FROM " . $this->nomeTabela . " WHERE nome LIKE '%" . $filtroTexto . "%' ORDER BY nome ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function alterarStatusEquipe($idEquipe, $novoStatus) {
        $query = 'UPDATE ' . $this->nomeTabela . ' SET status = :novo_status WHERE id = :id;';
        $stmt= $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':novo_status', $novoStatus, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $idEquipe, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function buscarTodasEquipesAtivas() {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE status = true ORDER BY nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarTecnicosDaEquipe($idEquipe) {
        $query = 'SELECT tblu.nome, tblu.cpf FROM tbl_usuarios AS tblu,
        tbl_tecnicos AS tblt, tbl_equipes AS tble
        WHERE tblu.id = tblt.usuario_id
        AND tble.id = :id_equipe
        ORDER BY tblu.nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}