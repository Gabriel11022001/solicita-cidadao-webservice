<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class ServicoDAO extends DAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarTipoServicoPeloNome($nome) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE nome = :nome;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function editarTipoServico($tipoServico) {
        $query = 'UPDATE tbl_servicos SET nome = :nome, descricao = :descricao, status = :status WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':nome', $tipoServico->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':descricao', $tipoServico->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $tipoServico->getStatus(), PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $tipoServico->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function alterarStatusTipoServico($tipoServico) {
        $query = 'UPDATE tbl_servicos SET status = :status WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':status', $tipoServico->getStatus(), PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $tipoServico->getId(), PDO::PARAM_INT);

        return $stmt->execute();
    }
}