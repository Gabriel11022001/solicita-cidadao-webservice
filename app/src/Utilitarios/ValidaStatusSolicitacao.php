<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

class ValidaStatusSolicitacao
{
    private static $statusSolicitacao = [
        'Aguardando encaminhamento',
        'Aguardando análise do perito',
        'Concluído',
        'Cancelado',
        'Aguardando tratamento',
        'Aguardando encaminhamento a equipe responsável',
        'Aprovado pelo perito',
        'Reprovado pelo perito'
    ];

    public static function validarStatusSolicitacao($statusInformado) {

    }
}