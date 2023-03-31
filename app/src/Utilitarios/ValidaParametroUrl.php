<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

use Exception;

class ValidaParametroUrl
{

    public static function validarParametroUrl($nomeParametro) {
        
        if (!isset($_GET[$nomeParametro])) {

            throw new Exception('O parâmetro informado não está presente na url!');
        }

        $parametro = $_GET[$nomeParametro];

        if (empty($parametro)) {

            throw new Exception('Não foi definido valor para o parâmetro informado!');
        }

    }
}