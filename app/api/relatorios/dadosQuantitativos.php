<?php

use SistemaSolicitacaoServico\App\Auth\Auth;
use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\UsuarioDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\ParametroRequisicao;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

try {
    Auth::validarToken();
    $idUsuario = intval(ParametroRequisicao::obterParametro('usuario_id'));
    $tipoUsuario = trim(ParametroRequisicao::obterParametro('tipo_usuario'));
    $errosDados = [];

    if (empty($idUsuario)) {
        $errosDados['usuario_id'] = 'Informe o id do usuário!';
    }

    if (empty($tipoUsuario)) {
        $errosDados['tipo_usuario'] = 'Informe o tipo de usuário!';
    }

    if (count($errosDados) > 0) {
        RespostaHttp::resposta('Informe todos os dados obrigatórios!', 200, $errosDados, false);
        exit;
    }

} catch (AuthException $e) {

} catch (Exception $e) {

}