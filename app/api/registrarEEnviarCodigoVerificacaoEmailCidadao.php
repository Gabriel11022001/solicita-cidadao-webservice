<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\CodigoRecuperacaoSenhaDAO;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;

try {
    $email = trim(ParametroRequisicao::obterParametro('email'));
    
    if (empty($email)) {
        RespostaHttp::resposta('Informe o e-mail!', 400, null);
        exit;
    }

    if (!ValidaEmail::validarEmail($email)) {
        RespostaHttp::resposta('E-mail inválido!', 400, null);
        exit;
    }

    $conexaoBancoDados = ConexaoBancoDados::obterConexao();
    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $cidadao = $cidadaoDAO->buscarCidadaoPeloEmail($email);

    if ($cidadao != false) {
        $codigoVerificacao = rand(10000, 99999);
        $idCidadao = $cidadao['id'];
        $codigoRecuperacaoSenhaDAO = new CodigoRecuperacaoSenhaDAO($conexaoBancoDados);

        if ($codigoRecuperacaoSenhaDAO->registrarCodigoRecuperacaoSenha($idCidadao, $codigoVerificacao)) {
            // realizar o envio do e-mail com o código de recuperação de senha
            
            //
            RespostaHttp::resposta('Caso exista um perfíl cadastrado com o e-mail informado, será enviado em instantes para esse e-mail um código de verificação!', 200, [
                'id_cidadao' > $idCidadao,
                'codigo_recuperacao_senha' => $codigoVerificacao
            ]);
        } else {
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o código de recuperação de senha!', 400, null);
        }

    } else {
        RespostaHttp::resposta('Não existe um cidadão cadastrado com esse e-mail no banco de dados!');
    }

} catch (Exception $e) {
    Log::registrarLog('Ocorreu um erro ao tentar-se recuperar a senha!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se realizar a recuperação de senha!', 400, null);   
}