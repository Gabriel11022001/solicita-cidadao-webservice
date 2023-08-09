<?php

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\CidadaoDAO;
use SistemaSolicitacaoServico\App\DAOS\CodigoRecuperacaoSenhaDAO;
use SistemaSolicitacaoServico\App\Utilitarios\GerenciadorEmail;
use SistemaSolicitacaoServico\App\Utilitarios\Log;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaEmail;

$conexaoBancoDados = ConexaoBancoDados::obterConexao();
$conexaoBancoDados->beginTransaction();

try {
    $email = trim(ParametroRequisicao::obterParametro('email'));
    
    if (empty($email)) {
        RespostaHttp::resposta('Informe o e-mail!', 200, null, false);
        exit;
    }

    if (!ValidaEmail::validarEmail($email)) {
        RespostaHttp::resposta('E-mail inválido!', 200, null, false);
        exit;
    }

    $cidadaoDAO = new CidadaoDAO($conexaoBancoDados, 'tbl_cidadaos');
    $cidadao = $cidadaoDAO->buscarCidadaoPeloEmail($email);

    if ($cidadao != false) {
        $codigoVerificacao = rand(10000, 99999);
        $idCidadao = $cidadao['id'];
        $codigoRecuperacaoSenhaDAO = new CodigoRecuperacaoSenhaDAO($conexaoBancoDados);
        
        if ($codigoRecuperacaoSenhaDAO->registrarCodigoRecuperacaoSenha($idCidadao, $codigoVerificacao)) {
            $estiloCodigoEmail = 'color: #2d3436; font-size: 30px; background-color: #dfe6e9; padding: 20px; text-align: center;';

            // realizar o envio do e-mail com o código de recuperação de senha
            if (!GerenciadorEmail::enviarEmail(
                $email,
                'Código de recuperação de senha:<br><h3 style="' . $estiloCodigoEmail . '">' . $codigoVerificacao . '</h3>',
                'Recuperação de senha'
            )) {
                $conexaoBancoDados->rollBack();
                RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o código de recuperação de senha!', 200, null, false);
                exit;
            }
            
            $conexaoBancoDados->commit();
            RespostaHttp::resposta('Caso exista um perfíl cadastrado com o e-mail informado, será enviado em instantes para esse e-mail um código de verificação!', 200, [
                'id' => $conexaoBancoDados->lastInsertId(),
                'id_cidadao' => $idCidadao,
                'codigo_recuperacao_senha' => $codigoVerificacao
            ]);
        } else {
            RespostaHttp::resposta('Ocorreu um erro ao tentar-se registrar o código de recuperação de senha!', 200, null, false);
        }

    } else {
        RespostaHttp::resposta('Não existe um cidadão cadastrado com esse e-mail no banco de dados!');
    }

} catch (Exception $e) {
    $conexaoBancoDados->rollBack();
    Log::registrarLog('Ocorreu um erro ao tentar-se recuperar a senha!', $e->getMessage());
    RespostaHttp::resposta('Ocorreu um erro ao tentar-se realizar a recuperação de senha!' . $e->getMessage(), 200, null, false);   
}