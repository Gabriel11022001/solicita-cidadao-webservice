<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\NotificacaoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();

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
        // alterando o status da notificação para "Visualizado"

        if ($notificacaoDAO->alterarStatusNotificacaoParaVisualizado($id)) {
            $notificacao['status'] = 'Visualizado';
            RespostaHttp::resposta('Notificação encontrada com sucesso e com status alterado com sucesso!', 200, $notificacao, true);
        } else {
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar o status da notificação para "Visualizado"!', 200, null, false);
        }

    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar a notificação pelo id!', $e->getMessage());
}