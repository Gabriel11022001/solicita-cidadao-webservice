<?php

namespace SistemaSolicitacaoServico\App\Auth;

use SistemaSolicitacaoServico\App\BancoDados\ConexaoBancoDados;
use SistemaSolicitacaoServico\App\DAOS\TokenDAO;
use SistemaSolicitacaoServico\App\Exceptions\AuthException;
use SistemaSolicitacaoServico\App\Utilitarios\RespostaHttp;

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

        if (array_key_exists('Authorization', $cabecalhos) && !isset($cabecalhos['Authorization'])) {

            throw new AuthException('O token para autenticação não foi informado!');
        }

        if (array_key_exists('authorization', $cabecalhos) && !isset($cabecalhos['authorization'])) {

            throw new AuthException('O token para autenticação não foi informado!');
        }

        $token = '';

        if (array_key_exists('Authorization', $cabecalhos)) {
            $token = $cabecalhos['Authorization'];
        } else {
            $token = $cabecalhos['authorization'];
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