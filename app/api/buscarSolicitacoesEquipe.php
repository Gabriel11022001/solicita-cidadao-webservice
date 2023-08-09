<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\DAOS\SolicitacaoServicoDAO;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;

try {
    Auth::validarToken();

    if (!isset($_GET['id_equipe'])) {
        RespostaHttp::resposta('Informe o id como parâmetro na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id_equipe']);

    if (empty($id)) {
        RespostaHttp::resposta('Informe o id da equipe!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');
    $solicitacaoServicoDAO = new SolicitacaoServicoDAO($conexaoBancoDados, 'tbl_solicitacoes_servico');

    if (!$equipeDAO->buscarPeloId($id)) {
        RespostaHttp::resposta('Não existe uma equipe cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    $solicitacoes = $solicitacaoServicoDAO->buscarSolicitacoesEquipe($id);

    if (count($solicitacoes) === 0) {
        RespostaHttp::resposta('Não existem solicitações de serviço cadastradas no banco de dados!', 200, null, false);
        exit;
    }

    foreach ($solicitacoes as $solicitacao) {
        $solicitacao['id'] = intval($solicitacao['id']);
    }

    if (count($solicitacoes) === 1) {
        RespostaHttp::resposta('Existe 1 solicitação cadastrada no banco de dados!', 200, $solicitacoes, true);
    } else {
        RespostaHttp::resposta('Existe um total de ' . count($solicitacoes) . ' solicitações cadastradas no banco de dados!', 200, $solicitacoes, true);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar as solicitações de serviço da equipe em questão!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar as solicitações de serviço da equipe em questão!', 200, null, false);
}