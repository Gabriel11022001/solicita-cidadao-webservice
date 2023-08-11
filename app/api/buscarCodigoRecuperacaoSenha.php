<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CodigoRecuperacaoSenhaDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    $codigo = intval(ParametroRequisicao::obterParametro('codigo'));

    if (empty($codigo)) {
        RespostaHttp::resposta('Informe o código de recuperação!', 200, null, false);
        exit;
    }

    if ($codigo < 10000 || $codigo > 99999) {
        RespostaHttp::resposta('Código inválido!', 200, null, false);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $codigoRecuperacaoDAO = new CodigoRecuperacaoSenhaDAO($conexaoBancoDados, 'tbl_codigos_recuperacao_senha');
    $codigoEncontrado = $codigoRecuperacaoDAO->buscarCodigoRecuperacaoSenha($codigo);

    if (!$codigoEncontrado) {
        RespostaHttp::resposta('Esse código não está cadastrado no banco de dados!', 200, null, false);
    } else {
        RespostaHttp::resposta('Código encontrado com sucesso!', 200, [
            'codigo' => $codigo
        ], true);
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se consultar o código de validação!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se consultar o código de validação!', 200, null, false);
}