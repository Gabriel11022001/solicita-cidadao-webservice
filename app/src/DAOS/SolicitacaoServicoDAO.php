<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class SolicitacaoServicoDAO extends DAO
{
    
    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarTodasSolicitacoesServicoCidadao($idCidadao) {
        $query = 'SELECT tblsc.id, tblsc.titulo, tblsc.posicao_fila,
        tblsc.protocolo, tblsc.prioridade, tblsc.data_registro,
        tblsc.status FROM ' . $this->nomeTabela . ' AS tblsc INNER JOIN tbl_usuarios AS tblu
        ON tblsc.cidadao_id = tblu.id AND tblu.id = :cidadao_id ORDER BY posicao_fila ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':cidadao_id', $idCidadao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}