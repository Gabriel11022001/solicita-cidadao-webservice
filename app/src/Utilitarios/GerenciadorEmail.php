<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

use PHPMailer\PHPMailer\PHPMailer;

class GerenciadorEmail
{

    /**
     * Método para realizar o envio de e-mails
     */
    public static function enviarEmail($emailDestinatario, $mensagem) {
        $email = new PHPMailer(true);
    }
}