<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Entidades\Instituicao;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\Log;

try {
    $instituicao = new Instituicao();
    $instituicao->setId(ParametroRequisicao::obterParametro('id'));
    $instituicao->setStatus(ParametroRequisicao::obterParametro('novo_status'));

    if (empty($instituicao->getId())) {
        RespostaHttp::resposta('Informe o id da instituição!', 200, null, false);
        exit;
    }

    if ($instituicao->getId() < 0) {
        RespostaHttp::resposta('O id da instituição deve ser um valor inteiro maior que 0!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $instituicaoDAO = new InstituicaoDAO($conexaoBancoDados, 'tbl_instituicoes');

    // verificando se existe uma instituição cadastrada com o id informado
    if (!$instituicaoDAO->buscarPeloId($instituicao->getId())) {
        RespostaHttp::resposta('Não existe uma instituição cadastrada no banco de dados com esse id!', 200, null, false);
        exit;
    }

    // alterando o status da instituição
    if ($instituicaoDAO->alterarStatusInstituicao($instituicao->getId(), $instituicao->getStatus())) {
        RespostaHttp::resposta('O status da instituição foi alterado com sucesso!', 200, [
            'id' => $instituicao->getId(),
            'novo_status' => $instituicao->getStatus()
        ]);
    } else {
        RespostaHttp::resposta('Ocorreu um erro ao tentar-se alterar o status da instituição em questão!', 200, null, false);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se alterar o status da instituição em questão!', $e->getMessage());
}