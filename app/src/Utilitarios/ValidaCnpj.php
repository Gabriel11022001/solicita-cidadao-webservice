<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class ValidaCnpj
{

    public static function validarCnpj($cnpj) {
        // Remove qualquer caracter que não seja número
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Verifica se o CNPJ tem 14 dígitos
        if (strlen($cnpj) != 14) {

            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {

            return false;
        }
        
        // Calcula os dígitos verificadores
        $dv1 = 0;

        for ($i = 0, $j = 5; $i < 12; $i++) {
            $dv1 += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $dv1 = ($dv1 % 11) < 2 ? 0 : 11 - ($dv1 % 11);
        
        $dv2 = 0;

        for ($i = 0, $j = 6; $i < 13; $i++) {
            $dv2 += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $dv2 = ($dv2 % 11) < 2 ? 0 : 11 - ($dv2 % 11);
        
        // Verifica se os dígitos verificadores estão corretos
        if ($cnpj[12] != $dv1 || $cnpj[13] != $dv2) {
            
            return false;
        }
        
        // CNPJ válido
        return true;
    }    
}