<?php

namespace SistemaSolicitacaoServico\App\DAOS;

class PeritoDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }
}