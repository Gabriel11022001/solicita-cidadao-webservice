<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class NotificacaoDAO extends DAO
{
    
    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarNotificacoesPeloIdUsuario($idUsuario) {
        $query = 'SELECT tbln.id, tbln.data_envio, tbln.status, tbls.titulo
        FROM ' . $this->nomeTabela . ' AS tbln, tbl_solicitacoes_servico AS tbls, 
        tbl_usuarios AS tblu WHERE tbln.usuario_id = tblu.id
        AND tbln.solicitacao_servico_id = tbls.id
        AND tblu.id = :usuario_id ORDER BY tbln.data_envio ASC;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':usuario_id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}