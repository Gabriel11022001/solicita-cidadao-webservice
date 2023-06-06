<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\DAOS\TecnicoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $idTecnico = intval(ParametroRequisicao::obterParametro('tecnico_id'));
    $idEquipe = intval(ParametroRequisicao::obterParametro('equipe_id'));
    $errosCampos = [];
    
    if (empty($idTecnico)) {
        $errosCampos['tecnico_id'] = 'Informe o id do técnico!';
    }

    if (empty($idEquipe)) {
        $errosCampos['equipe_id'] = 'Informe o id da equipe!';
    }

    if (count($errosCampos) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosCampos, false);
        exit;
    }

    if ($idTecnico <= 0) {
        $errosCampos['tecnico_id'] = 'O id do técnico não deve ser um valor menor que 1';
    }

    if ($idEquipe <= 0) {
        $errosCampos['equipe_id'] = 'O id da equipe não deve ser um valor menor que 1!';
    }

    if (count($errosCampos)) {
        RespostaHttp::resposta('Ocorreram erros de validação de dados!', 200, $errosCampos, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $tecnicoDAO = new TecnicoDAO($conexaoBancoDados, 'tbl_tecnicos');
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');
    
    if (!$tecnicoDAO->buscarPeloId($idTecnico)) {
        // não existe um técnico cadastrado no banco de dados com o id informado
        RespostaHttp::resposta('Não existe um técnico cadastrado no banco de dados com esse id!', 200, null, false);
        exit;
    }

    $equipe = $equipeDAO->buscarPeloId($idEquipe);
    if (!$equipe) {
        // não existe uma equipe cadastrada com esse id no banco de dados
        RespostaHttp::resposta('Não existe uma equipe cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    // validando se o status da equipe é ativo
    if (!$equipe['status']) {
        RespostaHttp::resposta('A equipe informada não está ativa!', 200, null, false);
        exit;
    }

    if ($tecnicoDAO->atribuirTecnicoAEquipe($idTecnico, $idEquipe)) {
        RespostaHttp::resposta('Atribuição de técnico a equipe ' . $equipe['nome'] . ' realizado com sucesso!', 200, [
            'tecnico_id' => $idTecnico,
            'equipe_id' => $idEquipe
        ], true);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se atribuir o técnico a uma equipe!', 200, null, false);
    }

} catch (Exception $e) {
    
}