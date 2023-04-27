<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class ValidaEmail
{

    public static function validarEmail($email) {
        // Define a expressão regular para validar o e-mail
        $regex = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
        
        // Executa a expressão regular no endereço de e-mail fornecido
        if (preg_match($regex, $email)) {
            
            return true; // O endereço de e-mail é válido
        } else {

            return false; // O endereço de e-mail é inválido
        }

    }
}