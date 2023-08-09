<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class ConfiguraRequisicoes
{
    
    public static function configurarRequisicoes() {
        date_default_timezone_set('America/Sao_Paulo');
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Authorization, Origin');
        header('Access-Control-Allow-Methods:  POST, PUT, GET, DELETE, OPTIONS');
    }
}