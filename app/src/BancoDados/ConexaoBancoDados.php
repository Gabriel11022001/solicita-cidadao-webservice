<?php

namespace SistemaSolicitacaoServico\App\BancoDados;

use PDO;

class ConexaoBancoDados
{
    
    public static function obterConexao() {
        $dadosCon = self::obterUsuarioESenhaBancoDados();
        $usuario = $dadosCon['usuario'];
        $senha = $dadosCon['senha'];
        $bancoDados = self::obterNomeBancoDados();
        $host = self::obterHost();
        $pdo = new PDO('pgsql:host=' . $host . ';dbname=' . $bancoDados, $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }

    private static function obterNomeBancoDados() {
        $config = file_get_contents(__DIR__ . '/../../config.json');
        $configObj = json_decode($config);

        if ($configObj->ambiente === 'dev') {

            return $configObj->db->banco_ambiente_local;
        }

        if ($configObj->ambiente === 'prod') {

            return $configObj->db->banco_ambiente_prod;
        }

        return $configObj->db->banco_ambiente_teste;
    }

    private static function obterUsuarioESenhaBancoDados() {
        $config = file_get_contents(__DIR__ . '/../../config.json');
        $configObj = json_decode($config);
        $dadosConexao = [];

        if ($configObj->ambiente === 'dev') {
            $dadosConexao['usuario'] = $configObj->usuarios_banco
                ->banco_ambiente_local
                ->usuario;
            $dadosConexao['senha'] = $configObj->usuarios_banco
                ->banco_ambiente_local
                ->senha;
        } elseif ($configObj->ambiente === 'prod') {
            $dadosConexao['usuario'] = $configObj->usuarios_banco
                ->banco_ambiente_prod
                ->usuario;
            $dadosConexao['senha'] = $configObj->usuarios_banco
                ->banco_ambiente_prod
                ->senha;
        } else {
            $dadosConexao['usuario'] = $configObj->usuarios_banco
                ->banco_ambiente_teste
                ->usuario;
            $dadosConexao['senha'] = $configObj->usuarios_banco
                ->banco_ambiente_teste
                ->senha;
        }

        return $dadosConexao;
    }

    private static function obterHost() {
        $config = file_get_contents(__DIR__ . '/../../config.json');
        $configObj = json_decode($config);

        if ($configObj->ambiente === 'dev') {

            return $configObj->hosts_bancos->banco_ambiente_local;
        }

        if ($configObj->ambiente === 'prod') {

            return $configObj->hosts_bancos->banco_ambiente_prod;
        }

        return $configObj->hosts_bancos->banco_ambiente_teste;
    }
}