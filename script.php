<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

define('OPTS', [
    (object) ['short' => 'h', 'long' => 'help', 'type' => 'no_value', 'comm' => 'Print help'],
]);

function usage()
{
    echo 'Usage :' . PHP_EOL;
    echo 'php scripts.php [OPTIONS] <Year> <Day> <Part> < input' . PHP_EOL;
    foreach (OPTS as $opt) {
        echo " -{$opt->short} --{$opt->long}\t{$opt->comm}" . ($opt->type === 'required' ? "\t*required" : '') . PHP_EOL;
    }

    die();
}

$shortOpts = '';
$longOpts = [];
foreach (OPTS as $opt) {
    $type = match ($opt->type) {
        'required' => ':',
        'optionnal' => '::',
        default => '',
    };

    $shortOpts .= "{$opt->short}{$type}";
    $longOpts[] = "{$opt->long}{$type}";
}

$opts = getopt($shortOpts, $longOpts);
foreach (OPTS as $opt) {
    if (isset($opts[$opt->short])) {
        $opts[$opt->long] = $opts[$opt->short];
        unset($opts[$opt->short]);
    }
}
$opts = new Collection($opts);

if ($opts->has('help') || $argc < 4) {
    usage();
}

list($script, $year, $day, $part) = $argv;

$challengeClass = "\\App\\Y{$year}\\D{$day}\\P{$part}\\Challenge";

if (!class_exists($challengeClass)) {
    echo "Unknown challenge {$challengeClass}" . PHP_EOL;
    usage();
}

$input = new LazyCollection(function () {
    $fp = fopen('php://stdin', 'r');
    while ($line = fgets($fp)) {
        yield $line;
    }

    fclose($fp);
});

$challenge = new $challengeClass($input);

echo $challenge->resolve() . PHP_EOL;

