<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');
    $equipesAtivas = $equipeDAO->buscarTodasEquipesAtivas();

    if (count($equipesAtivas) === 0) {
        RespostaHttp::resposta('Não existem equipes ativas cadastradas no banco de dados!');
    } else {

        foreach ($equipesAtivas as $equipe) {
            $equipe['id'] = intval($equipe['id']);
        }

        if (count($equipesAtivas) === 1) {
            RespostaHttp::resposta('Existe 1 equipe ativa cadastrada no banco de dados!', 200, $equipesAtivas, true);
        } else {
            RespostaHttp::resposta('Existe um total de ' . count($equipesAtivas) . ' equipes ativas cadastradas no banco de dados!', 200, $equipesAtivas, true);
        }

    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todas as equipes ativas!', $e->getMessage());
}