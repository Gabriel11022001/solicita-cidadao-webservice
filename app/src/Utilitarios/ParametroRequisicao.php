<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

use Exception;

class ParametroRequisicao
{
    
    public static function obterParametro($parametro) {
        $dadosCorpoRequisicao = file_get_contents('php://input');
        $dadosCorpoRequisicaoObj = json_decode($dadosCorpoRequisicao);
        $propriedadesObjetosRequisicao = get_object_vars($dadosCorpoRequisicaoObj);

        if (!key_exists($parametro, $propriedadesObjetosRequisicao)) {
            throw new Exception('No objeto json não existe uma propriedade definida com esse nome!');
        }

        return $propriedadesObjetosRequisicao[$parametro];
    }
}