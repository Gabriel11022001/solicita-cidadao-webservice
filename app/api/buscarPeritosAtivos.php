<?php

use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\PeritoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $peritoDAO = new PeritoDAO($conexaoBancoDados, 'tbl_peritos');
    $peritosAtivos = $peritoDAO->buscarTodosPeritosAtivos();

    if (count($peritosAtivos) === 0) {
        RespostaHttp::resposta('Não existem peritos ativos cadastrados no banco de dados!', 200, [], true);
    } else {

        foreach ($peritosAtivos as $peritoAtivo) {
            $peritoAtivo['id'] = intval($peritoAtivo['id']);
            $peritoAtivo['usuario_id'] = intval($peritoAtivo['usuario_id']);
        }

        if (count($peritosAtivos) === 1) {
            RespostaHttp::resposta('Existe 1 perito ativo cadastrado no banco de dados!', 200, $peritosAtivos,true);
        } else {
            RespostaHttp::resposta('Existe um total de ' . count($peritosAtivos) . ' peritos ativos cadastrados no banco de dados!', 200, $peritosAtivos, true);
        }

    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar todos os peritos ativos!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar todos os peritos ativos!', 200, null, false);
}