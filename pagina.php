<?php

declare(strict_types=1);

/** @var array $view Dados preparados pelo AguaController */

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function campo(string $nome, string $rotulo, array $valores): void
{
    echo '<div class="mb-3">';
    echo '<label class="form-label" for="' . e($nome) . '">' . e($rotulo) . '</label>';
    echo '<input class="form-control" type="number" step="any" min="0" required '
        . 'id="' . e($nome) . '" name="' . e($nome) . '" value="' . e($valores[$nome] ?? '') . '">';
    echo '</div>';
}

function selo(string $situacao): string
{
    return match ($situacao) {
        'potavel' => '<span class="badge text-bg-success">Potável</span>',
        'alerta' => '<span class="badge text-bg-warning">Alerta</span>',
        default => '<span class="badge text-bg-danger">Não potável</span>',
    };
}

$valores = $view['valores'];
$resultado = $view['resultado'];
$erro = $view['erro'];

$rotulosParametros = [
    'ph' => 'pH',
    'turbidez' => 'Turbidez (uT)',
    'cloro' => 'Cloro (mg/L)',
    'dureza' => 'Dureza (mg/L)',
    'temperatura' => 'Temperatura (°C)',
    'solidosTotais' => 'Sólidos totais (mg/L)',
];

$rotulosParecer = [
    'POTAVEL' => ['Potável', 'text-bg-success'],
    'ALERTA' => ['Alerta', 'text-bg-warning'],
    'NAO_POTAVEL' => ['Não potável', 'text-bg-danger'],
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratório Digital de Qualidade da Água — ODS 6</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<header class="py-4 mb-4 border-bottom bg-white">
    <div class="container" style="max-width: 960px;">
        <span class="badge text-bg-primary mb-2">ODS 6 — Água potável e saneamento</span>
        <h1 class="h3 mb-1">Laboratório Digital de Qualidade da Água</h1>
        <p class="text-secondary mb-0">
            Informe as medições feitas antes e depois do biofiltro. O sistema classifica cada
            parâmetro segundo a Portaria GM/MS 888/2021 e calcula a eficiência do filtro.
        </p>
    </div>
</header>

<main class="container pb-5" style="max-width: 960px;">

    <?php if ($erro): ?>
        <div class="alert alert-danger" role="alert">
            <strong>Não foi possível analisar a amostra.</strong> Revise os campos e tente de novo.
            <div class="small mt-1">Detalhe: <?= e($erro) ?></div>
        </div>
    <?php endif; ?>

    <form method="post" class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <fieldset class="col-md-6">
                    <legend class="h6 text-uppercase text-secondary">Antes do filtro</legend>
                    <?php
                    campo('ph_antes', 'pH', $valores);
                    campo('turbidez_antes', 'Turbidez (uT)', $valores);
                    campo('cloro_antes', 'Cloro residual (mg/L)', $valores);
                    campo('dureza_antes', 'Dureza (mg/L)', $valores);
                    campo('temperatura_antes', 'Temperatura (°C)', $valores);
                    campo('solidos_antes', 'Sólidos dissolvidos totais (mg/L)', $valores);
                    ?>
                </fieldset>
                <fieldset class="col-md-6">
                    <legend class="h6 text-uppercase text-secondary">Depois do filtro</legend>
                    <?php
                    campo('ph_depois', 'pH', $valores);
                    campo('turbidez_depois', 'Turbidez (uT)', $valores);
                    campo('cloro_depois', 'Cloro residual (mg/L)', $valores);
                    campo('dureza_depois', 'Dureza (mg/L)', $valores);
                    campo('temperatura_depois', 'Temperatura (°C)', $valores);
                    campo('solidos_depois', 'Sólidos dissolvidos totais (mg/L)', $valores);
                    ?>
                </fieldset>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">Analisar amostra</button>
            <button type="submit" class="btn btn-outline-secondary" formaction="exportar.php" formmethod="post">
                Baixar dataset (CSV)
            </button>
        </div>
    </form>

    <?php if ($resultado && !$erro): ?>
        <?php
        $parecerDepois = $resultado['avaliacaoDepois']['parecer'];
        [$textoParecer, $classeParecer] = $rotulosParecer[$parecerDepois];
        ?>
        <section class="card shadow-sm mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Resultado da análise</strong>
                <span class="badge <?= e($classeParecer) ?>">Água filtrada: <?= e($textoParecer) ?></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach (['avaliacaoAntes' => 'Antes do filtro', 'avaliacaoDepois' => 'Depois do filtro'] as $chave => $titulo): ?>
                        <div class="col-md-6">
                            <h2 class="h6 text-uppercase text-secondary"><?= e($titulo) ?></h2>
                            <table class="table table-sm">
                                <tbody>
                                <?php foreach ($resultado[$chave]['parametros'] as $parametro => $situacao): ?>
                                    <tr>
                                        <td><?= e($rotulosParametros[$parametro]) ?></td>
                                        <td class="text-end"><?= selo($situacao) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>

                <h2 class="h6 text-uppercase text-secondary mt-3">Eficiência do biofiltro</h2>
                <div class="row">
                    <?php foreach ($resultado['eficiencia'] as $parametro => $taxa): ?>
                        <div class="col-md-4 mb-2">
                            <div class="border rounded p-2 text-center">
                                <div class="small text-secondary"><?= e($rotulosParametros[$parametro]) ?></div>
                                <div class="fs-5"><?= number_format((float) $taxa, 1, ',', '.') ?>%</div>
                                <div class="small">remoção <?= e(Lab\Modelos\Biofiltro::rotuloEficiencia((float) $taxa)) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

</main>

<footer class="border-top py-3 text-center text-secondary small">
    Faixas de referência: pH 6,0–9,5 · Turbidez ≤ 5 uT · Cloro 0,2–2 mg/L ·
    Dureza ≤ 500 mg/L · TDS ≤ 500 mg/L — Portaria GM/MS 888/2021 e OMS.
</footer>

</body>
</html>
