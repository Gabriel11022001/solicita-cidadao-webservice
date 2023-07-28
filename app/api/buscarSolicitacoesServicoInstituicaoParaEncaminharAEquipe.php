<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();

    if (!isset($_GET['instituicao_id'])) {
        RespostaHttp::resposta('O parâmetro não foi informado na url!', 2900, null, false);
        exit;
    }

    $idInstituicao = intval($_GET['instituicao_id']);

    if (empty($idInstituicao)) {
        RespostaHttp::resposta('Informe o id da instituição!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');
    $solicitacoesInstituicao = $solicitacaoServicoDAO->buscarSolicitacoesServicoInstituicaoParaEncaminharParaEquipe($idInstituicao);

    if (count($solicitacoesInstituicao) === 0) {
        RespostaHttp::resposta('Não existem solicitações para essa instituição cadastradas no banco de dados!');
    } else {

        foreach ($solicitacoesInstituicao as $solicitacaoInstituicao) {
            $solicitacaoInstituicao['id'] = intval($solicitacaoInstituicao['id']);
        }

        if (count($solicitacoesInstituicao) === 1) {
            RespostaHttp::resposta('Existe 1 solicitação cadastrada no banco de dados para essa instituição!', 200, $solicitacoesInstituicao, true);
        } else {
            RespostaHttp::resposta('Existe um total de ' . count($solicitacoesInstituicao) . ' solicitações cadastradas no banco de dados para essa instituição!', 200, $solicitacoesInstituicao, true);
        }

    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar as solicitações de serviço para serem encaminhadas as equipes!', $e->getMessage());
}