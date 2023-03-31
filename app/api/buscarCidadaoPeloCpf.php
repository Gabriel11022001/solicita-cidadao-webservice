<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaParametroUrl;

try {
    ValidaParametroUrl::validarParametroUrl('cpf');
    $cpf = $_GET['cpf'];
    
    if (!ValidaCpf::validarCPF($cpf)) {
        RespostaHttp::resposta('O cpf informado é inválido!', 400, null);
    } else {
        $conexaoBancoDados = ConexaoBancoDados::obterConexao();
        $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
        $cidadao = $cidadaoDAO->buscarPeloCpf($cpf);

        if ($cidadao != false) {
            RespostaHttp::resposta('Cidadão encontrado com sucesso!', 200, $cidadao);
        } else {    
            RespostaHttp::resposta('Não existe um cidadão cadastrado com esse cpf!');
        }

    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se buscar um cidadão pelo cpf!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar o cidadão pelo cpf!', 400, null);
}