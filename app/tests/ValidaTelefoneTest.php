<?php

use PHPUnit\Framework\TestCase;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaTelefone;

class ValidaTelefoneTest extends TestCase
{
    
    /**
     * @test
     */
    public function testeQuandoTelefoneEhValido() {
        $telefone = '(14) 99877-3214';
        $telefoneEhValido = ValidaTelefone::validarTelefone($telefone);
        $this->assertTrue($telefoneEhValido);
    }

    /**
     * @test
     */
    public function testeQuandoTelefoneNaoPossuiQuantidadeCorretaDeDigitos() {
        $telefone = '(14) 9988-76';
        $telefoneEhValido = ValidaTelefone::validarTelefone($telefone);
        $this->assertFalse($telefoneEhValido);
    }

    /**
     * @test
     */
    public function testeQuandoTelefoneNaoPossuiCaracteresEspeciaisEPossuiSomenteNumeros() {
        $telefone = '14998776655';
        $telefoneEhValido = ValidaTelefone::validarTelefone($telefone);
        $this->assertTrue($telefoneEhValido);
    }
}