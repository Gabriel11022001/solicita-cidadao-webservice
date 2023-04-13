<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class CodigoRecuperacaoSenhaDAO
{
    private $conexaoBancoDados;

    public function __construct($conexaoBancoDados) {
        $this->conexaoBancoDados = $conexaoBancoDados;
    }

    public function registrarCodigoRecuperacaoSenha($idCidadao, $codigo) {
        $query = 'INSERT INTO tbl_codigos_recuperacao_senha(cidadao_id, codigo)
        VALUES(:cidadao_id, :codigo);';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':cidadao_id', $idCidadao, PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $codigo, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function buscarCodigoRecuperacaoSenha($id) {
        $query = 'SELECT * FROM tbl_codigos_recuperacao_senha
        WHERE id = :id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}