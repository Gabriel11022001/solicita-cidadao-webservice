<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class GerenciadorEmail
{

    /**
     * Método para realizar o envio de e-mails
     */
    public static function enviarEmail($emailDestinatario, $mensagem, $assunto) {
        $email = new PHPMailer(true);
        $email->SMTPDebug = SMTP::DEBUG_SERVER;
        $email->isSMTP();
        $email->Host = 'smtp.gmail.com';
        $email->SMTPAuth = true;
        $email->Username = 'solicitacidadao.email.teste@gmail.com';
        $email->Password = 'doeuqcgdnyfdozgt';
        $email->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $email->Port = 465;
        // dados de quem envia o e-mail
        $email->setFrom('solicitacidadao.email.teste@gmail.com', 'Solicita cidadão');
        // dados do destinatário
        $email->addAddress($emailDestinatario);
        // assunto do e-mail
        $email->Subject = $assunto;
        // corpo do e-mail
        $email->CharSet = 'UTF-8';
        $email->isHTML();
        $email->Body = $mensagem;

        // efetivando o envio do e-mail
        return $email->send();
    }
}