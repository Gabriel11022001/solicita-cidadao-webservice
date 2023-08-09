<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();

    if (!isset($_GET['filtro_texto'])) {
        RespostaHttp::resposta('O parâmetro não foi definido na url para consulta!', 200, null, false);
        exit;
    }
    
    $filtroTexto = mb_strtoupper(trim($_GET['filtro_texto']));
    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');
    $equipes = $equipeDAO->buscarEquipeComFiltroDeTexto($filtroTexto);

    if (count($equipes) === 0) {
        RespostaHttp::resposta('Não existem equipes relacionadas ao filtro aplicado cadastradas no banco de dados!', 200, []);
    } else {
        RespostaHttp::resposta('Foram encontradas ' . count($equipes) . ' equipes relacionadas ao filtro aplicado!', 200, $equipes, true);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar a equipe com filtro de texto!', $e->getMessage());
}