<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class RespostaHttp
{

    public static function resposta($mensagem, $codigoHttp = 200, $conteudo = null) {
        http_response_code($codigoHttp);
        echo json_encode([
            'mensagem' => $mensagem,
            'conteudo' => $conteudo
        ]);
    }
}