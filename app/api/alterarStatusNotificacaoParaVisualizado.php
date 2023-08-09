<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\NotificacaoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();
    $id = trim(ParametroRequisicao::obterParametro('id'));

    if (empty($id)) {
        RespostaHttp::resposta('Informe o id da notificação!', 200, null, false);
        exit;
    }

    $id = intval($id);
    
    if ($id <= 0) {
        RespostaHttp::resposta('O id da notificação deve ser um valor maior que 0!', 200, null, false);
        exit;
    }
    
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $notificacaoDAO = new NotificacaoDAO($conexaoBancoDados, 'tbl_notificacoes');

    if (!$notificacaoDAO->buscarNotificacaoPeloId($id)) {
        RespostaHttp::resposta('Não existe uma notificação cadastrada no banco de dados com esse id!');
        exit;
    }

    if ($notificacaoDAO->alterarStatusNotificacaoParaVisualizado($id)) {
        RespostaHttp::resposta('Status alterado para visualizado com sucesso!');
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar o status da notificação para visualizado!', 200, null, false);
    }
    
} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se alterar o status da notificação para visualizado!', $e->getMessage());
    var_dump($e->getMessage());
}