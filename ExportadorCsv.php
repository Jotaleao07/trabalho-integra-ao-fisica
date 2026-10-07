<?php

declare(strict_types=1);

namespace Lab\Controle;

/** Gera o CSV do dataset para baixar pela interface. */
final class ExportadorCsv
{
    public function exportar(array $post): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="dataset-qualidade-agua.csv"');

        $saida = fopen('php://output', 'wb');
        fputcsv($saida, ['parametro', 'antes_do_filtro', 'depois_do_filtro'], ',', '"', '\\');

        $linhas = [
            ['pH', 'ph_antes', 'ph_depois'],
            ['Turbidez (uT)', 'turbidez_antes', 'turbidez_depois'],
            ['Cloro residual (mg/L)', 'cloro_antes', 'cloro_depois'],
            ['Dureza (mg/L)', 'dureza_antes', 'dureza_depois'],
            ['Temperatura (°C)', 'temperatura_antes', 'temperatura_depois'],
            ['Solidos dissolvidos totais (mg/L)', 'solidos_antes', 'solidos_depois'],
        ];

        foreach ($linhas as [$rotulo, $campoAntes, $campoDepois]) {
            fputcsv($saida, [$rotulo, $post[$campoAntes] ?? '', $post[$campoDepois] ?? ''], ',', '"', '\\');
        }

        fclose($saida);
    }
}
