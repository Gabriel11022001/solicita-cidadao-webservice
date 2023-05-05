<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\InstituicaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    
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
        RespostaHttp::resposta('Instituição encontrada com sucesso!', 200, $instituicao, true);
    }

} catch (Exception $e) {

}