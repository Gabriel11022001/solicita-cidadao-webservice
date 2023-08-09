<?php

namespace SistemaSolicitacaoServico\App\Auth;

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\TokenDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;

class Auth
{

    public static function gerarToken() {
        $token = md5(microtime(true).mt_Rand());
        $conexaoBancoDados = ConexaoBancoDados::obterConexao();
        
        if (!TokenDAO::cadastrarToken($conexaoBancoDados, $token)) {

            throw new AuthException('Ocorreu um erro tentar-se gerar o token para autenticação!');
        }

        return $token;
    }

    public static function validarToken() {
        $cabecalhos = getallheaders();
        
        if (array_key_exists('X-Auth-Token', $cabecalhos) && !isset($cabecalhos['X-Auth-Token'])) {

            throw new AuthException('O token para autenticação não foi informado!');
        }

        if (array_key_exists('x-auth-token', $cabecalhos) && !isset($cabecalhos['x-auth-token'])) {

            throw new AuthException('O token para autenticação não foi informado!');
        }
        
        $token = '';

        if (array_key_exists('X-Auth-Token', $cabecalhos)) {
            $token = $cabecalhos['X-Auth-Token'];
        } else {
            $token = $cabecalhos['x-auth-token'];
        }

        if (empty($token)) {

            throw new AuthException('O token para autenticação não foi informado!');
        }

        $conexaoBancoDados = ConexaoBancoDados::obterConexao();

        if (!TokenDAO::buscarToken($conexaoBancoDados, $token)) {

            throw new AuthException('Você não está autorizado para fazer requisições!');
        }

    }
}