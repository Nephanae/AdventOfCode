<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Collection;

function getDigits()
{
    return [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        'one' => 1,
        'two' => 2,
        'three' => 3,
        'four' => 4,
        'five' => 5,
        'six' => 6,
        'seven' => 7,
        'eight' => 8,
        'nine' => 9,
    ];
}

function findFirstDigit($string)
{
    $result = false;
    foreach (getDigits() as $digitString => $digit) {
        $pos = strpos($string, $digitString);
        if ($pos !== false && ($result === false || $pos < $result->pos)) {
            $result = (object) ['pos' => $pos, 'digit' => $digit];
        }
    }

    return $result->digit ?? false;
};

$input = new Collection(explode(PHP_EOL, file_get_contents('php://stdin')));

echo $input
    ->filter()
    ->map(function ($row) {
        $digits = '';
        while (($digit = findFirstDigit($row)) !== false) {
            $digits .= $digit;
            $row = substr($row, 1);
        }

        return substr($digits, 0, 1) . substr($digits, -1);
    })
    ->sum();

echo PHP_EOL;
