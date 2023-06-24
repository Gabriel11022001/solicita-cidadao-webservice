<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\NotificacaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id deve ser informado na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);

    if (empty($id)) {
        RespostaHttp::resposta('Informe o id do cidadão!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $notificacaoDAO = new NotificacaoDAO($conexaoBancoDados, 'tbl_notificacoes');
    $notificacoes = $notificacaoDAO->buscarNotificacoesPeloIdUsuario($id);

    if (count($notificacoes) === 0) {
        RespostaHttp::resposta('Não existem notificações cadastradas no banco de dados!', 200, [], true);
    } else {

        foreach ($notificacoes as $notificacao) {
            $notificacao['id'] = intval($notificacao['id']);
        }

        if (count($notificacoes) === 1) {
            RespostaHttp::resposta('Existe 1 notificação cadastrada no banco de dados!', 200, $notificacoes, true);
        } else {
            RespostaHttp::resposta('Existe um total de ' . count($notificacoes) . ' notificações cadastradas no banco de dados!', 200, $notificacoes, true);
        }

    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todas as notificações do cidadão!', $e->getMessage());
}