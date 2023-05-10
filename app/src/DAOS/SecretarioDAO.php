<?php

namespace SistemaSolicitacaoServico\App\DAOS;

class SecretarioDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }
}