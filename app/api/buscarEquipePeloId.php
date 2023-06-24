<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id não está definido como parâmetro na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);

    if (empty($id)) {
        RespostaHttp::resposta('Informe o id da equipe!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');
    $equipe = $equipeDAO->buscarPeloId($id);

    if ($equipe) {
        RespostaHttp::resposta('Equipe encontrada com sucesso!', 200, $equipe, true);
    } else {
        RespostaHttp::resposta('Não existe uma equipe cadastrada com esse id no banco de dados!');
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar a equipe pelo id!', $e->getMessage());
}