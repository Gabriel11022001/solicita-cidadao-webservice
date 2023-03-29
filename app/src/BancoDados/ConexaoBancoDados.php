<?php

namespace SistemaSolicitacaoServico\App\BancoDados;

use PDO;

class ConexaoBancoDados
{
    
    public static function obterConexao() {
        $usuario = 'root';
        $senha = 'root';
        $bancoDados = 'db_ws_solicitacoes_servico_secretaria';
        $pdo = new PDO('pgsql:host=postgreSQL_db;dbname=' . $bancoDados, $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
}