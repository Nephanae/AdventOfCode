<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

$input = new LazyCollection(function () {
    $fp = fopen('php://stdin', 'r');
    while ($line = fgets($fp)) {
        yield $line;
    }

    fclose($fp);
});

$balls = (object) [
    'red' => 12,
    'green' => 13,
    'blue' => 14,
];

echo $input
    ->map(fn($row) => explode(':', $row))
    ->mapWithKeys(fn($row) => [substr($row[0], 5) => new Collection(explode(';', $row[1]))])
    ->map(
        fn($turns) => $turns
            ->map(fn($turn) => new Collection(explode(',', $turn)))
            ->map(
                fn($turn) => $turn
                    ->map(fn($set) => explode(' ', trim($set)))
                    ->mapWithKeys(fn($set) => [$set[1] => $set[0]])
            )
    )
    ->filter(fn($game) => $game->every(fn($sets) => $sets->every(fn($count, $color) => $count <= $balls->$color)))
    ->keys()
    ->sum();

echo PHP_EOL;

