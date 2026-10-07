<?php

declare(strict_types=1);

namespace Testes;

use InvalidArgumentException;
use Lab\Modelos\Biofiltro;
use Lab\Modelos\ClassificadorAgua;
use PHPUnit\Framework\TestCase;

final class BiofiltroTest extends TestCase
{
 // ---------- Taxa de remoção ----------

    public function testTaxaRemocaoComValoresConhecidos(): void
    {
        // (100 - 20) / 100 * 100 = 80%
        $this->assertEqualsWithDelta(80.0, Biofiltro::taxaRemocao(100.0, 20.0), 0.001);
        // (50 - 20) / 50 * 100 = 60%
        $this->assertEqualsWithDelta(60.0, Biofiltro::taxaRemocao(50.0, 20.0), 0.001);
    }

    public function testTaxaRemocaoTotal(): void
    {
        $this->assertEqualsWithDelta(100.0, Biofiltro::taxaRemocao(10.0, 0.0), 0.001);
    }

    public function testTaxaNegativaIndicaPiora(): void
    {
        $this->assertEqualsWithDelta(-50.0, Biofiltro::taxaRemocao(10.0, 15.0), 0.001);
    }

    public function testTaxaComValorInicialZeroLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Biofiltro::taxaRemocao(0.0, 5.0);
    }

    public function testTaxaComValoresNegativosLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Biofiltro::taxaRemocao(-1.0, 5.0);
    }

    // ---------- Aplicar taxa e camadas ----------

    public function testAplicarTaxa(): void
    {
        $this->assertEqualsWithDelta(80.0, Biofiltro::aplicarTaxa(100.0, 20.0), 0.001);
    }

    public function testAplicarTaxaInvalidaLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Biofiltro::aplicarTaxa(100.0, 101.0);
    }

    public function testSimularDuasCamadasDeCinquentaPorCento(): void
    {
        // 100 -> 50 -> 25
        $this->assertEqualsWithDelta(25.0, Biofiltro::simularCamadas(100.0, [50.0, 50.0]), 0.001);
    }

    public function testSimularSemCamadasMantemOValor(): void
    {
        $this->assertEqualsWithDelta(100.0, Biofiltro::simularCamadas(100.0, []), 0.001);
    }

    // ---------- Eficiência por parâmetro ----------

    public function testEficienciaPorParametro(): void
    {
        $eficiencia = Biofiltro::eficienciaPorParametro(
            ['turbidez' => 10.0, 'solidosTotais' => 500.0],
            ['turbidez' => 2.0, 'solidosTotais' => 250.0]
        );
        $this->assertEqualsWithDelta(80.0, $eficiencia['turbidez'], 0.001);
        $this->assertEqualsWithDelta(50.0, $eficiencia['solidosTotais'], 0.001);
    }

    public function testEficienciaSemParDepoisLancaExcecao(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Biofiltro::eficienciaPorParametro(['turbidez' => 10.0], ['dureza' => 5.0]);
    }

    // ---------- Rótulo de eficiência ----------

    public function testRotuloEficiencia(): void
    {
        $this->assertSame('piora', Biofiltro::rotuloEficiencia(-5.0));
        $this->assertSame('baixa', Biofiltro::rotuloEficiencia(10.0));
        $this->assertSame('moderada', Biofiltro::rotuloEficiencia(50.0));
        $this->assertSame('alta', Biofiltro::rotuloEficiencia(80.0));
    }

    // ---------- Integração: classificação + biofiltro ----------

    public function testFiltroMelhoraParecerDaAmostra(): void
    {
        $antes = [
            'ph' => 7.0, 'turbidez' => 8.0, 'cloro' => 0.1,
            'dureza' => 600.0, 'temperatura' => 28.0, 'solidosTotais' => 800.0,
        ];
        $depois = [
            'ph' => 7.2, 'turbidez' => 2.0, 'cloro' => 1.0,
            'dureza' => 300.0, 'temperatura' => 24.0, 'solidosTotais' => 400.0,
        ];

        $eficiencia = Biofiltro::eficienciaPorParametro(
            ['turbidez' => $antes['turbidez'], 'solidosTotais' => $antes['solidosTotais']],
            ['turbidez' => $depois['turbidez'], 'solidosTotais' => $depois['solidosTotais']]
        );

        $this->assertGreaterThan(0, $eficiencia['turbidez']);
        $this->assertSame('NAO_POTAVEL', ClassificadorAgua::avaliarAmostra($antes)['parecer']);
        $this->assertSame('POTAVEL', ClassificadorAgua::avaliarAmostra($depois)['parecer']);
    }
}
