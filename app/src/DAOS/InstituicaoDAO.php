<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use JetBrains\PhpStorm\Internal\ReturnTypeContract;
use PDO;

class InstituicaoDAO extends DAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarInstituicaoPeloNome($nome) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE nome = :nome;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarInstituicaoPeloCnpj($cnpj) {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE cnpj = :cnpj;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':cnpj', $cnpj, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarInstituicaoComFiltroDeTexto($filtroTexto) {
        $query = "SELECT * FROM " . $this->nomeTabela . " WHERE nome LIKE '%" . $filtroTexto . "%' OR cnpj LIKE '%" . $filtroTexto . "%';";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}