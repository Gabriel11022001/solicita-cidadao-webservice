<?php

namespace SistemaSolicitacaoServico\App\Exceptions;

use Exception;

class ExtensaoArquivoInvalidoException extends Exception
{

    public function __construct($mensagem) {
        parent::__construct($mensagem);
    }
}