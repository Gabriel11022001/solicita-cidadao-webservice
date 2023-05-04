<?php

namespace SistemaSolicitacaoServico\App\Utilitarios;

use DateTime;

class ValidaDataNascimento
{

    public static function validarFormatoDataNascimento($data) {
        $dataFormatada = date('d-m-Y', strtotime($data));

        return $dataFormatada === $data;
    }

    public static function validarSeDataNascimentoEhPosteriorADataAtual($dataNascimento) {
        $dataAtual = new DateTime('now');

        if ($dataNascimento > $dataAtual) {

            return true;
        }

        return false;
    }

    public static function validarSeDataNascimentoEhMuitoAntiga($dataNascimento) {
        $dataAtual = new DateTime('now');

        if ($dataAtual == $dataNascimento) {

            return false;
        }

        $diferencaEmAnos = $dataAtual->diff($dataNascimento)->y;

        if ($diferencaEmAnos >= 120) {

            return true;
        }

        return false;
    }
}