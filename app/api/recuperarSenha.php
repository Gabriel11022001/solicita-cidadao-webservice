<?php

use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;

try {
    $novaSenha = trim(ParametroRequisicao::obterParametro('nova_senha'));
    $senhaConfirmacao = trim(ParametroRequisicao::obterParametro('senha_confirmacao'));
} catch (Exception $e) {
    
}