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
        $perfis = [];
        $conexaoBancoDados = ConexaoBancoDados::obterConexao();
        $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
        // buscando um cidadão com o cpf informado
        $cidadao = $cidadaoDAO->buscarPeloCpf($cpf);

        if ($cidadao != false) {
            $perfis['perfil_cidadao'] = $cidadao;
        }

        if (count($perfis) > 0) {
            RespostaHttp::resposta('Existem os seguintes perfis cadastrados com esse cpf!', 200, $perfis);
        } else {
            RespostaHttp::resposta('Não existem perfis cadastrados com esse cpf!');
        }

    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu o seguinte erro ao tentar-se buscar os perfis vinculados ao cpf em questão!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se buscar os perfis vinculados ao cpf em questão!', 400, null);
}