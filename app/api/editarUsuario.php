<?php

use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    
} catch (AuthException $e) {
    Log::registrarLog($e->getMessage(), $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar os dados do usuário!', 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se editar os dados do usuário!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se editar os dados do usuário!', 200, null, false);
}