<?php
require __DIR__ . '/vendor/autoload.php';

use App\App;

$app = new App();

if ($argc < 4) {
    $app->usage();
}

list($script, $year, $day, $part) = $argv;

$app->run($year, $day, $part);

