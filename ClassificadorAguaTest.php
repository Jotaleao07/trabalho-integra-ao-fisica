<?php

declare(strict_types=1);

namespace Testes;

use InvalidArgumentException;
use Lab\Modelos\ClassificadorAgua;
use PHPUnit\Framework\TestCase;

final class ClassificadorAguaTest extends TestCase
{
    // ---------- pH ----------

    public function testPhDentroDaFaixaEPotavel(): void
    {
        $this->assertSame('potavel', ClassificadorAgua::ph(7.0));
    }

    public function testPhNosLimitesDaFaixaEPotavel(): void
    {
        $this->assertSame('potavel', ClassificadorAgua::ph(6.0));
        $this->assertSame('potavel', ClassificadorAgua::ph(9.5));
    }

    public function testPhForaDaFaixaNaoEPotavel(): void
    {
        $this->assertSame('nao_potavel', ClassificadorAgua::ph(5.9));
        $this->assertSame('nao_potavel', ClassificadorAgua::ph(9.6));
    }

    public function testPhFisicamenteImpossivelLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClassificadorAgua::ph(15.0);
    }

    public function testPhAusenteLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClassificadorAgua::ph(null);
    }

    // ---------- Turbidez ----------

    public function testTurbidezNoLimiteEPotavel(): void
    {
        $this->assertSame('potavel', ClassificadorAgua::turbidez(5.0));
        $this->assertSame('nao_potavel', ClassificadorAgua::turbidez(5.1));
    }

    public function testTurbidezNegativaLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClassificadorAgua::turbidez(-1.0);
    }

    // ---------- Cloro ----------

    public function testCloroNaFaixaRecomendada(): void
    {
        $this->assertSame('potavel', ClassificadorAgua::cloro(0.2));
        $this->assertSame('potavel', ClassificadorAgua::cloro(2.0));
    }

    public function testCloroAbaixoDoMinimoNaoEPotavel(): void
    {
        $this->assertSame('nao_potavel', ClassificadorAgua::cloro(0.1));
    }

    public function testCloroAcimaDoRecomendadoFicaEmAlerta(): void
    {
        $this->assertSame('alerta', ClassificadorAgua::cloro(3.0));
        $this->assertSame('nao_potavel', ClassificadorAgua::cloro(5.1));
    }

    // ---------- Dureza ----------

    public function testDurezaNoLimite(): void
    {
        $this->assertSame('potavel', ClassificadorAgua::dureza(500.0));
        $this->assertSame('nao_potavel', ClassificadorAgua::dureza(500.1));
    }

    // ---------- Temperatura ----------

    public function testTemperatura(): void
    {
        $this->assertSame('potavel', ClassificadorAgua::temperatura(25.0));
        $this->assertSame('alerta', ClassificadorAgua::temperatura(28.0));
        $this->assertSame('nao_potavel', ClassificadorAgua::temperatura(31.0));
    }

    public function testTemperaturaImprovavelLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClassificadorAgua::temperatura(150.0);
    }

    // ---------- Sólidos totais ----------

    public function testSolidosTotais(): void
    {
        $this->assertSame('potavel', ClassificadorAgua::solidosTotais(500.0));
        $this->assertSame('alerta', ClassificadorAgua::solidosTotais(700.0));
        $this->assertSame('nao_potavel', ClassificadorAgua::solidosTotais(1000.1));
    }

    // ---------- Parecer final da amostra ----------

    public function testAmostraTodaBoaEPotavel(): void
    {
        $amostra = [
            'ph' => 7.0, 'turbidez' => 2.0, 'cloro' => 1.0,
            'dureza' => 200.0, 'temperatura' => 22.0, 'solidosTotais' => 300.0,
        ];
        $this->assertSame('POTAVEL', ClassificadorAgua::avaliarAmostra($amostra)['parecer']);
    }

    public function testAmostraComParametroEmAlertaFicaEmAlerta(): void
    {
        $amostra = [
            'ph' => 7.0, 'turbidez' => 2.0, 'cloro' => 1.0,
            'dureza' => 200.0, 'temperatura' => 28.0, 'solidosTotais' => 300.0,
        ];
        $this->assertSame('ALERTA', ClassificadorAgua::avaliarAmostra($amostra)['parecer']);
    }

    public function testAmostraComUmParametroRuimENaoPotavel(): void
    {
        $amostra = [
            'ph' => 5.0, 'turbidez' => 2.0, 'cloro' => 1.0,
            'dureza' => 200.0, 'temperatura' => 22.0, 'solidosTotais' => 300.0,
        ];
        $this->assertSame('NAO_POTAVEL', ClassificadorAgua::avaliarAmostra($amostra)['parecer']);
    }

    public function testAmostraComCampoFaltandoLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClassificadorAgua::avaliarAmostra(['ph' => 7.0]);
    }

    public function testAmostraComValorNaoNumericoLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ClassificadorAgua::avaliarAmostra([
            'ph' => 'sete', 'turbidez' => 2.0, 'cloro' => 1.0,
            'dureza' => 200.0, 'temperatura' => 22.0, 'solidosTotais' => 300.0,
        ]);
    }
}
