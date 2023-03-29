<?php

require_once 'vendor/autoload.php';

use SistemaSolicitacaoServico\App\Utilitarios\ConfiguraRequisicoes;
use SistemaSolicitacaoServico\App\Utilitarios\Rota;

$uri = $_SERVER['REQUEST_URI'];
$uri = str_replace('/index.php', '', $uri);
$metodoHttp = $_SERVER['REQUEST_METHOD'];
ConfiguraRequisicoes::configurarRequisicoes();
Rota::implementarRequisicao($uri, $metodoHttp);
