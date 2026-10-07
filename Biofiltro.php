<?php

declare(strict_types=1);

namespace Lab\Modelos;

use InvalidArgumentException;

/**
 * Modelo matematico do biofiltro experimental: calcula a taxa de remocao
 * de um parametro, aplica taxas de remocao (uma camada ou varias em
 * sequencia) e resume a eficiencia do filtro.
 */
final class Biofiltro
{
    /**
     * Taxa de remocao em porcentagem: (antes - depois) / antes * 100.
     * Valor negativo significa que o parametro piorou apos o filtro.
     */
    public static function taxaRemocao(float $antes, float $depois): float
    {
        if ($antes < 0 || $depois < 0) {
            throw new InvalidArgumentException('Valores nao podem ser negativos');
        }
        if ($antes == 0.0) {
            throw new InvalidArgumentException('Valor inicial zero: impossivel calcular a taxa (divisao por zero)');
        }
        return ($antes - $depois) / $antes * 100;
    }

    /** Aplica uma taxa de remocao (0 a 100%) sobre uma concentracao. */
    public static function aplicarTaxa(float $concentracao, float $taxaPercentual): float
    {
        if ($concentracao < 0) {
            throw new InvalidArgumentException('Concentracao nao pode ser negativa');
        }
        if ($taxaPercentual < 0 || $taxaPercentual > 100) {
            throw new InvalidArgumentException('A taxa deve estar entre 0 e 100%');
        }
        return $concentracao * (1 - $taxaPercentual / 100);
    }

    /**
     * Simula a passagem por varias camadas do biofiltro, cada uma com sua
     * taxa de remocao. Ex.: [50, 50] remove 50% e depois 50% do restante.
     *
     * @param list<float> $taxasPorCamada
     */
    public static function simularCamadas(float $concentracao, array $taxasPorCamada): float
    {
        foreach ($taxasPorCamada as $taxa) {
            $concentracao = self::aplicarTaxa($concentracao, (float) $taxa);
        }
        return $concentracao;
    }

    /**
     * Calcula a taxa de remocao de cada parametro medido antes e depois.
     *
     * @param array<string,float> $antes
     * @param array<string,float> $depois
     * @return array<string,float>
     */
    public static function eficienciaPorParametro(array $antes, array $depois): array
    {
        $eficiencia = [];
        foreach ($antes as $parametro => $valorAntes) {
            if (!array_key_exists($parametro, $depois)) {
                throw new InvalidArgumentException('Falta o valor "depois" de ' . $parametro);
            }
            $eficiencia[$parametro] = self::taxaRemocao((float) $valorAntes, (float) $depois[$parametro]);
        }
        return $eficiencia;
    }

    /** Rotulo qualitativo da eficiencia do filtro. */
    public static function rotuloEficiencia(float $taxa): string
    {
        if ($taxa < 0) {
            return 'piora';
        }
        if ($taxa < 30) {
            return 'baixa';
        }
        if ($taxa < 70) {
            return 'moderada';
        }
        return 'alta';
    }
}
