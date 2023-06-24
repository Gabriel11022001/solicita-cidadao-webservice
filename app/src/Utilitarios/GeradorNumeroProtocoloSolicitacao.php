<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

use DateTime;

class GeradorNumeroProtocoloSolicitacao
{

    public static function gerarNumeroProtocolo($idSolicitacao) {
        $dataAtual = new DateTime('now');

        return md5($idSolicitacao . $dataAtual->format('d-m-Y H:i:s'));
    }
}