<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\GestorInstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    Auth::validarToken();

    if (!isset($_GET['id'])) {
        RespostaHttp::resposta('O id não está definido como parâmetro na url!', 200, null, false);
        exit;
    }

    $id = intval($_GET['id']);

    if (empty($id)) {
        RespostaHttp::resposta('Informe o id da instituição!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');
    $instituicao = $instituicaoDAO->buscarPeloId($id);

    if (!$instituicao) {
        RespostaHttp::resposta('Não existe uma instituição cadastrada com esse id no banco de dados!');
    } else {
        $instituicao['id'] = intval($instituicao['id']);
        // buscando todos os técnicos e gestores de instituição relacionados a instituição
        $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
        $gestorInstituicaoDAO = new GestorInstituicaoDAO($conexaoBancoDados, 'tbl_gestores_instituicao');
        $tecnicos = $tecnicoDAO->buscarTecnicosPeloIdDaInstituicao($instituicao['id']);
        $gestoresInstituicao = $gestorInstituicaoDAO->buscarGestoresInstituicaoPeloIdDaInstituicao($instituicao['id']);

        if (count($tecnicos) === 0) {
            // a instituição não não possui técnicos
            $instituicao['tecnicos'] = [];
        } else {
            $instituicao['tecnicos'] = $tecnicos;
        }
        
        if (count($gestoresInstituicao) === 0) {
            // a instituição não possui gestores
            $instituicao['gestores_instituicao'] = [];
        } else {
            $instituicao['gestores_instituicao'] = $gestoresInstituicao;
        }
        
        RespostaHttp::resposta('Instituição encontrada com sucesso!', 200, $instituicao, true);
    }

} catch (AuthException $e) {
    Log::registrarLog('Erro de autenticação!', $e->getMessage());
    RespostaHttp::resposta($e->getMessage(), 200, null, false);
} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar a instituição pelo id!', $e->getMessage());
}