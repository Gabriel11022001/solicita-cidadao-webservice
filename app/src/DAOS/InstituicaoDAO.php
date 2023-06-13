<?php

namespace SistemaSolicitacaoServico\App\DAOS;

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
        $query = "SELECT * FROM " . $this->nomeTabela . " WHERE nome LIKE '%" . $filtroTexto . "%' OR cnpj LIKE '%" . $filtroTexto . "%' ORDER BY nome ASC;";
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function alterarStatusInstituicao($id, $novoStatus) {
        $query = 'UPDATE ' . $this->nomeTabela . ' SET status = :status WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':status', $novoStatus, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    public function editarInstituicao($dadosEditar) {
        $query = 'UPDATE ' . $this->nomeTabela . ' SET nome = :nome, descricao = :descricao,
        observacao = :observacao, cnpj = :cnpj, email = :email, telefone = :telefone,
        status = :status, logradouro = :logradouro, bairro = :bairro, estado = :estado,
        cidade = :cidade, complemento = :complemento, numero = :numero, cep = :cep
        WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $dadosEditar['id']['dado'], $dadosEditar['id']['tipo_dado']);
        $stmt->bindValue(':nome', $dadosEditar['nome']['dado'], $dadosEditar['nome']['tipo_dado']);
        $stmt->bindValue(':descricao', $dadosEditar['descricao']['dado'], $dadosEditar['descricao']['tipo_dado']);
        $stmt->bindValue(':status', $dadosEditar['status']['dado'], $dadosEditar['status']['tipo_dado']);
        $stmt->bindValue(':email', $dadosEditar['email']['dado'], $dadosEditar['email']['tipo_dado']);
        $stmt->bindValue(':cnpj', $dadosEditar['cnpj']['dado'], $dadosEditar['cnpj']['tipo_dado']);
        $stmt->bindValue(':telefone', $dadosEditar['telefone']['dado'], $dadosEditar['telefone']['tipo_dado']);
        $stmt->bindValue(':observacao', $dadosEditar['observacao']['dado'], $dadosEditar['observacao']['tipo_dado']);
        $stmt->bindValue(':logradouro', $dadosEditar['logradouro']['dado'], $dadosEditar['logradouro']['tipo_dado']);
        $stmt->bindValue(':bairro', $dadosEditar['bairro']['dado'], $dadosEditar['bairro']['tipo_dado']);
        $stmt->bindValue(':complemento', $dadosEditar['complemento']['dado'], $dadosEditar['complemento']['tipo_dado']);
        $stmt->bindValue(':cidade', $dadosEditar['cidade']['dado'], $dadosEditar['cidade']['tipo_dado']);
        $stmt->bindValue(':estado', $dadosEditar['estado']['dado'], $dadosEditar['estado']['tipo_dado']);
        $stmt->bindValue(':numero', $dadosEditar['numero']['dado'], $dadosEditar['numero']['tipo_dado']);
        $stmt->bindValue(':cep', $dadosEditar['cep']['dado'], $dadosEditar['cep']['tipo_dado']);

        return $stmt->execute();
    }

    public function buscarTodasInstituicoesOrdenandoPeloNomeDeFormaAscendente() {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' ORDER BY nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarTodasInstituicoesAtivas() {
        $query = 'SELECT * FROM ' . $this->nomeTabela . ' WHERE status = true ORDER BY nome ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}