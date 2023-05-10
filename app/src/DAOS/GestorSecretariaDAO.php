<?php

namespace SistemaSolicitacaoServico\App\DAOS;

class GestorSecretariaDAO extends UsuarioDAO
{

    public function __construct($conexaoBancoDados, $nomeTabela) {
        parent::__construct($conexaoBancoDados, $nomeTabela);
    }
}