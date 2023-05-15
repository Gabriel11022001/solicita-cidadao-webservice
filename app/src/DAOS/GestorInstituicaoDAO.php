<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class GestorInstituicaoDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }

    public function buscarGestoresInstituicaoPeloIdDaInstituicao($idInstituicao) {
        $query = 'SELECT u.nome, u.cpf FROM tbl_usuarios AS u INNER JOIN ' . $this->nomeTabela . ' AS gi
        ON u.id = gi.usuario_id AND gi.instituicao_id = :instituicao_id;';
        $stmt = $this->conexaoBancoDados->prepare($query);
        $stmt->bindValue(':instituicao_id', $idInstituicao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}