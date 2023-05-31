<?php

use PHPUnit\Framework\TestCase;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaCpf;

class ValidaCpfTest extends TestCase
{

    /**
     * @test
     */
    public function testeInformarCpfComFormatoInvalido() {
        $cpf = '487.123.456-9';
        $cpfEhValido = ValidaCpf::validarCPF($cpf);
        $this->assertFalse($cpfEhValido);
    }

    /**
     * @test
     */
    public function testeInformarCpfComNumeracaoInvalida() {
        $cpf = '123.456.789-00';
        $cpfEhValido = ValidaCpf::validarCPF($cpf);
        $this->assertFalse($cpfEhValido);
    }

    /**
     * @test
     */
    public function testeInformarCpfValido() {
        $cpf = '354.960.440-81';
        $cpfEhValido = ValidaCpf::validarCPF($cpf);
        $this->assertTrue($cpfEhValido);
    }

    /**
     * @test
     */
    public function testeQuandoCpfEhUmaStringVazia() {
        $cpf = '';
        $cpfEhValido = ValidaCpf::validarCPF($cpf);
        $this->assertFalse($cpfEhValido);
    }
}