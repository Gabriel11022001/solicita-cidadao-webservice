<?php

namespace SistemaSolicitacaoServico\App\BancoDados;

use PDO;

class ConexaoBancoDados
{
    
    public static function obterConexao() {
        $usuario = 'root';
        $senha = 'root';
        $bancoDados = 'solicita_cidadao_presidente_prudente';
        $pdo = new PDO('pgsql:host=postgreSQL_db;dbname=' . $bancoDados, $usuario, $senha);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
}