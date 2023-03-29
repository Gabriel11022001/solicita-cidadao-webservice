<?php

namespace SistemaSolicitacaoServico\App\DAOS;

class SolicitacaoServicoDAO extends DAO
{
    
    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }
}