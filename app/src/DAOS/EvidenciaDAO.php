<?php

namespace SistemaSolicitacaoServico\App\DAOS;

class EvidenciaDAO extends DAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }
}