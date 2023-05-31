<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\EquipeDAO;
use SistemaSolicitacaoServico\App\Entidades\Equipe;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $equipe = new Equipe();
    $equipe->setId(ParametroRequisicao::obterParametro('id'));
    $equipe->setStatus(ParametroRequisicao::obterParametro('novo_status'));

    if (empty($equipe->getId())) {
        RespostaHttp::resposta('Informe o id da equipe!', 200, null, false);
        exit;
    }

    if ($equipe->getId() < 0) {
        RespostaHttp::resposta('O id da equipe não deve ser menor que 0!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $equipeDAO = new EquipeDAO($conexaoBancoDados, 'tbl_equipes');

    if (!$equipeDAO->buscarPeloId($equipe->getId())) {
        RespostaHttp::resposta('Não existe uma equipe cadastrada no banco de dados com o id informado!', 200, null, false);
        exit;
    }

    if ($equipeDAO->alterarStatusEquipe($equipe->getId(), $equipe->getStatus())) {
        // status da equipe alterado com sucesso
        $dadosRetorno = [
            'id' => $equipe->getId(),
            'novo_status' => $equipe->getStatus()
        ];
        RespostaHttp::resposta('O status da equipe foi alterado com sucesso!', 200, $dadosRetorno, true);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar o status da equipe!', 200, null, false);
    }
    
} catch (Exception $e) {
    var_dump($e->getMessage());
}