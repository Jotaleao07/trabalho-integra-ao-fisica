<?php

declare(strict_types=1);

namespace Lab\Controle;

use Lab\Modelos\Biofiltro;
use Lab\Modelos\ClassificadorAgua;
use Lab\Modelos\RepositorioAmostras;
use Throwable;

/**
 * Recebe os dados do formulario, roda os algoritmos e prepara tudo
 * que a pagina precisa mostrar.
 */
final class AguaController
{
    private const CAMPOS = [
        'ph_antes', 'turbidez_antes', 'cloro_antes', 'dureza_antes', 'temperatura_antes', 'solidos_antes',
        'ph_depois', 'turbidez_depois', 'cloro_depois', 'dureza_depois', 'temperatura_depois', 'solidos_depois',
    ];

    public function __construct(private readonly RepositorioAmostras $repositorio)
    {
    }

    /** Processa o formulario e devolve os dados para a view. */
    public function processar(array $post): array
    {
        $valores = $this->lerCampos($post);
        $view = ['valores' => $valores, 'resultado' => null, 'erro' => null];

        if (($GLOBALS['REQUEST_METHOD'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $view;
        }

        try {
            $antes = $this->montarAmostra($valores, 'antes');
            $depois = $this->montarAmostra($valores, 'depois');

            $avaliacaoAntes = ClassificadorAgua::avaliarAmostra($antes);
            $avaliacaoDepois = ClassificadorAgua::avaliarAmostra($depois);
            $eficiencia = Biofiltro::eficienciaPorParametro(
                ['turbidez' => $antes['turbidez'], 'dureza' => $antes['dureza'], 'solidosTotais' => $antes['solidosTotais']],
                ['turbidez' => $depois['turbidez'], 'dureza' => $depois['dureza'], 'solidosTotais' => $depois['solidosTotais']]
            );

            $this->repositorio->salvar([
                'antes' => $antes,
                'depois' => $depois,
                'parecer_antes' => $avaliacaoAntes['parecer'],
                'parecer_depois' => $avaliacaoDepois['parecer'],
                'eficiencia' => $eficiencia,
                'data' => date('c'),
            ]);

            $view['resultado'] = [
                'avaliacaoAntes' => $avaliacaoAntes,
                'avaliacaoDepois' => $avaliacaoDepois,
                'eficiencia' => $eficiencia,
            ];
        } catch (Throwable $e) {
            $view['erro'] = $e->getMessage();
        }

        return $view;
    }

    /** Converte os campos do formulario em numeros (ou null se vazio/invalido). */
    private function lerCampos(array $post): array
    {
        $valores = [];
        foreach (self::CAMPOS as $campo) {
            $bruto = $post[$campo] ?? null;
            $valores[$campo] = is_numeric($bruto) && trim((string) $bruto) !== ''
                ? (float) $bruto
                : null;
        }
        return $valores;
    }

    private function montarAmostra(array $valores, string $momento): array
    {
        return [
            'ph' => $valores["ph_$momento"],
            'turbidez' => $valores["turbidez_$momento"],
            'cloro' => $valores["cloro_$momento"],
            'dureza' => $valores["dureza_$momento"],
            'temperatura' => $valores["temperatura_$momento"],
            'solidosTotais' => $valores["solidos_$momento"],
        ];
    }
}
