<?php

namespace SistemaSolicitacaoServico\App\DAOS;

use PDO;

class TokenDAO
{   

    public static function cadastrarToken($conexaoBancoDados, $token) {
        $query = 'INSERT INTO tbl_tokens(token) VALUES(:token);';
        $stmt = $conexaoBancoDados->prepare($query);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public static function buscarToken($conexaoBancoDados, $token) {
        $query = 'SELECT token FROM tbl_tokens WHERE token = :token;';
        $stmt = $conexaoBancoDados->prepare($query);
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}