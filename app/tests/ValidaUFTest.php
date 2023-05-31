<?php

use PHPUnit\Framework\TestCase;
use SistemaSolicitacaoServico\App\Utilitarios\ValidaUF;

class ValidaUFTest extends TestCase
{

    /**
     * @test
     */
    public function testeQuandoUfInformadaEhValida() {
        $uf = 'SP';
        $ufEhValida = ValidaUF::validarUF($uf);
        $this->assertEquals('ok', $ufEhValida);
    }

    /**
     * @test
     */
    public function testeQuandoUfInformadaEhValidaPoremTodaMinuscula() {
        $uf = 'pr';
        $ufEhValida = ValidaUF::validarUF($uf);
        $this->assertEquals('ok', $ufEhValida);
    }

    /**
     * @test
     */
    public function testeQuandoUfInformadaPossuiMaisDeDoisCaracteres() {
        $uf = 'spppppppppppppppppppppppppppppppp';
        $ufEhValida = ValidaUF::validarUF($uf);
        $this->assertEquals('A unidade federativa precisa possuir exatamente 2 caracteres!', $ufEhValida);
    }

    /**
     * @test
     */
    public function testeQuandoUfInformadaPossuiMenosDeDoisCaracteres() {
        $uf = 's';
        $ufEhValida = ValidaUF::validarUF($uf);
        $this->assertEquals('A unidade federativa precisa possuir exatamente 2 caracteres!', $ufEhValida);
    }

    /**
     * @test
     */
    public function testeQuandoUfInformadaNaoEhUmaUfExistenteNoBrasil() {
        $uf = 'yz';
        $ufEhValida = ValidaUF::validarUF($uf);
        $this->assertEquals('A unidade federativa informada é incorreta!', $ufEhValida);
    }
}