<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Collection;

$input = new Collection(explode(PHP_EOL, file_get_contents('php://stdin')));

echo $input
    ->filter()
    ->map(fn($row) => new Collection(str_split($row)))
    ->map(fn($row) => $row->filter(fn($char) => is_numeric($char)))
    ->map(fn($row) => $row->first() . $row->last())
    ->sum();

echo PHP_EOL;
