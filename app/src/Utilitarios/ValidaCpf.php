<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class ValidaCpf
{

    public static function validarCPF($cpf) {
        // Remove pontos e traços do CPF
        $cpf = preg_replace('/[^\d]/', '', $cpf);
    
        // Verifica se o CPF possui 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
    
        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1+$/', $cpf)) {
            return false;
        }
    
        // Calcula o primeiro dígito verificador
        $soma = 0;

        for ($i = 0; $i < 9; $i++) {
            $soma += (int) $cpf[$i] * (10 - $i);
        }

        $digito1 = 11 - ($soma % 11);

        if ($digito1 >= 10) {
            $digito1 = 0;
        }
    
        // Calcula o segundo dígito verificador
        $soma = 0;

        for ($i = 0; $i < 10; $i++) {
            $soma += (int) $cpf[$i] * (11 - $i);
        }

        $digito2 = 11 - ($soma % 11);

        if ($digito2 >= 10) {
            $digito2 = 0;
        }
    
        // Verifica se os dígitos calculados são iguais aos dígitos informados
        if ($cpf[9] == $digito1 && $cpf[10] == $digito2) {

            return true;
        } else {
            
            return false;
        }
    }
}