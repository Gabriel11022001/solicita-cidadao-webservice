<?php

namespace SistemaSolicitacaoServico\App\Entidades;

class Cidadao extends Usuario
{
    private $cidadaoId;

    public function setCidadaoId($cidadaoId) {
        $this->cidadaoId = $cidadaoId;
    }

    public function getCidadaoId() {

        return $this->cidadaoId;
    }
}