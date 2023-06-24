<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\NotificacaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    
    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O parâmetro não foi definido na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);

    if (empty($id)) {
        RespostaHttp::resposta('Informe o id da notificação!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $notificacaoDAO = new NotificacaoDAO($conexaoBancoDados, 'tbl_notificacoes');
    $notificacao = $notificacaoDAO->buscarNotificacaoPeloId($id);

    if (!$notificacao) {
        RespostaHttp::resposta('Não existe uma notificação cadastrada no banco de dados com esse id!');
    } else {
        RespostaHttp::resposta('Notificação encontrada com sucesso!', 200, $notificacao, true);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar a notificação pelo id!', $e->getMessage());
}