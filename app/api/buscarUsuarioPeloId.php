<?php

use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id não está definido como parâmetro na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);

} catch (Exception $e) {
    
}