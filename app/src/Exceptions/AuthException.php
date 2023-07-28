<?php

namespace SistemaSolicitacaoServico\App\Exceptions;

use Exception;

class AuthException extends Exception
{

    public function __construct($mensagem) {
        parent::__construct($mensagem);
    }
}