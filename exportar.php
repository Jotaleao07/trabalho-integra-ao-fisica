<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Lab\Controle\ExportadorCsv;

(new ExportadorCsv())->exportar($_POST);
