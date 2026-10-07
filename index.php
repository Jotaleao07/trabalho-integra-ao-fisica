<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Lab\Controle\AguaController;
use Lab\Modelos\RepositorioAmostras;

$controller = new AguaController(new RepositorioAmostras(__DIR__ . '/dados/amostras.json'));
$view = $controller->processar($_POST);

require __DIR__ . '/src/visao/pagina.php';
