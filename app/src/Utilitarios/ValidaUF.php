<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class ValidaUF
{

    public static function validarUF($uf) {

        if (strlen($uf) != 2) {

            return 'A unidade federativa precisa possuir exatamente 2 caracteres!';
        }

        $estados = array(
            'AC', // Acre
            'AL', // Alagoas
            'AP', // Amapá
            'AM', // Amazonas
            'BA', // Bahia
            'CE', // Ceará
            'DF', // Distrito Federal
            'ES', // Espírito Santo
            'GO', // Goiás
            'MA', // Maranhão
            'MT', // Mato Grosso
            'MS', // Mato Grosso do Sul
            'MG', // Minas Gerais
            'PA', // Pará
            'PB', // Paraíba
            'PR', // Paraná
            'PE', // Pernambuco
            'PI', // Piauí
            'RJ', // Rio de Janeiro
            'RN', // Rio Grande do Norte
            'RS', // Rio Grande do Sul
            'RO', // Rondônia
            'RR', // Roraima
            'SC', // Santa Catarina
            'SP', // São Paulo
            'SE', // Sergipe
            'TO', // Tocantins
        );
        $uf = strtoupper($uf);

        if (!in_array($uf, $estados)) {

            return 'A unidade federativa informada é incorreta!';
        }

        return 'ok';
    }
}