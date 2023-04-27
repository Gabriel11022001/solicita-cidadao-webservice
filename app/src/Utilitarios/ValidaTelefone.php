<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class ValidaTelefone
{

    public static function validarTelefone($telefone) {
        // Remove qualquer caracter que não seja número
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        // Verifica se o telefone tem 10 ou 11 dígitos
        if (!preg_match('/^([0-9]{10,11})$/', $telefone)) {

            return false;
        }
        
        // Verifica se o telefone está no formato (00) 00000-0000 ou (00) 0000-0000
        if (!preg_match('/^\(?\d{2}\)?\s?\d{4,5}\-?\d{4}$/', $telefone)) {
            
            return false;
        }
        
        // Telefone válido
        return true;
    }
}