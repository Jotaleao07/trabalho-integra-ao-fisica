<?php

declare(strict_types=1);

namespace Lab\Modelos;

use InvalidArgumentException;

/**
 * Classifica cada parametro da agua segundo as faixas de potabilidade
 * (Portaria GM/MS 888/2021 e diretrizes da OMS) e gera o parecer final
 * da amostra.
 */
final class ClassificadorAgua
{
    public const POTAVEL = 'potavel';
    public const ALERTA = 'alerta';
    public const NAO_POTAVEL = 'nao_potavel';

    /** pH potavel entre 6,0 e 9,5 (inclusive). */
    public static function ph(?float $valor): string
    {
        $valor = self::exigirNumero($valor, 'pH');
        if ($valor < 0 || $valor > 14) {
            throw new InvalidArgumentException('pH fora da escala fisica (0 a 14): ' . $valor);
        }
        return ($valor >= 6.0 && $valor <= 9.5) ? self::POTAVEL : self::NAO_POTAVEL;
    }

    /** Turbidez potavel ate 5 uT. */
    public static function turbidez(?float $valor): string
    {
        $valor = self::exigirNumero($valor, 'turbidez');
        if ($valor < 0) {
            throw new InvalidArgumentException('Turbidez nao pode ser negativa');
        }
        return $valor <= 5.0 ? self::POTAVEL : self::NAO_POTAVEL;
    }

    /** Cloro residual livre: 0,2 a 2 mg/L recomendado; ate 5 mg/L e alerta. */
    public static function cloro(?float $valor): string
    {
        $valor = self::exigirNumero($valor, 'cloro');
        if ($valor < 0) {
            throw new InvalidArgumentException('Cloro nao pode ser negativo');
        }
        if ($valor < 0.2) {
            return self::NAO_POTAVEL; // desinfeccao insuficiente
        }
        if ($valor <= 2.0) {
            return self::POTAVEL;
        }
        if ($valor <= 5.0) {
            return self::ALERTA;
        }
        return self::NAO_POTAVEL;
    }

    /** Dureza potavel ate 500 mg/L de CaCO3. */
    public static function dureza(?float $valor): string
    {
        $valor = self::exigirNumero($valor, 'dureza');
        if ($valor < 0) {
            throw new InvalidArgumentException('Dureza nao pode ser negativa');
        }
        return $valor <= 500.0 ? self::POTAVEL : self::NAO_POTAVEL;
    }

    /** Temperatura: ate 25 °C adequada, 26-30 °C alerta, acima disso inadequada. */
    public static function temperatura(?float $valor): string
    {
        $valor = self::exigirNumero($valor, 'temperatura');
        if ($valor < -10 || $valor > 100) {
            throw new InvalidArgumentException('Temperatura fisicamente improvavel: ' . $valor);
        }
        if ($valor <= 25.0) {
            return self::POTAVEL;
        }
        if ($valor <= 30.0) {
            return self::ALERTA;
        }
        return self::NAO_POTAVEL;
    }

    /** Solidos dissolvidos totais: ate 500 mg/L potavel, ate 1000 mg/L alerta. */
    public static function solidosTotais(?float $valor): string
    {
        $valor = self::exigirNumero($valor, 'solidos totais');
        if ($valor < 0) {
            throw new InvalidArgumentException('Solidos totais nao pode ser negativo');
        }
        if ($valor <= 500.0) {
            return self::POTAVEL;
        }
        if ($valor <= 1000.0) {
            return self::ALERTA;
        }
        return self::NAO_POTAVEL;
    }

    /**
     * Avalia a amostra completa e devolve a classificacao de cada parametro
     * mais o parecer final: NAO_POTAVEL se qualquer parametro estiver fora,
     * ALERTA se algum estiver em alerta, senao POTAVEL.
     *
     * @param array{ph:?float,turbidez:?float,cloro:?float,dureza:?float,temperatura:?float,solidosTotais:?float} $amostra
     * @return array{parametros:array<string,string>,parecer:string}
     */
    public static function avaliarAmostra(array $amostra): array
    {
        $campos = ['ph', 'turbidez', 'cloro', 'dureza', 'temperatura', 'solidosTotais'];
        foreach ($campos as $campo) {
            if (!array_key_exists($campo, $amostra) || $amostra[$campo] === null || !is_numeric($amostra[$campo])) {
                throw new InvalidArgumentException('Campo ausente ou invalido: ' . $campo);
            }
        }

        $parametros = [
            'ph' => self::ph((float) $amostra['ph']),
            'turbidez' => self::turbidez((float) $amostra['turbidez']),
            'cloro' => self::cloro((float) $amostra['cloro']),
            'dureza' => self::dureza((float) $amostra['dureza']),
            'temperatura' => self::temperatura((float) $amostra['temperatura']),
            'solidosTotais' => self::solidosTotais((float) $amostra['solidosTotais']),
        ];

        $parecer = 'POTAVEL';
        if (in_array(self::NAO_POTAVEL, $parametros, true)) {
            $parecer = 'NAO_POTAVEL';
        } elseif (in_array(self::ALERTA, $parametros, true)) {
            $parecer = 'ALERTA';
        }

        return ['parametros' => $parametros, 'parecer' => $parecer];
    }

    private static function exigirNumero(?float $valor, string $nome): float
    {
        if ($valor === null) {
            throw new InvalidArgumentException('Valor ausente para ' . $nome);
        }
        return $valor;
    }
}
