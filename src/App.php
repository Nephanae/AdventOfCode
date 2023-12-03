<?php
namespace App;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

final class App
{
    const OPTS = [
        ['short' => 'h', 'long' => 'help', 'type' => 'no_value', 'comm' => 'Print help'],
    ];

    private Collection $opts;

    public function __construct()
    {
        $this->opts = $this->buildOpts();
    }

    public function getOpts(): Collection
    {
        return $this->opts;
    }

    public function run($year, $day, $part)
    {
        if ($this->opts->has('help')) {
            $app->usage();
        }

        $challengeClass = "\\App\\Y{$year}\\D{$day}\\P{$part}\\Challenge";

        if (!class_exists($challengeClass)) {
            echo "Unknown challenge {$challengeClass}" . PHP_EOL;
            usage();
        }
        
        $challenge = new $challengeClass($this->getInput());

        echo $challenge->resolve() . PHP_EOL;
    }

    public function usage()
    {
        echo 'Usage :' . PHP_EOL;
        echo 'php scripts.php [OPTIONS] <Year> <Day> <Part> < input' . PHP_EOL;
        foreach (self::OPTS as $opt) {
            echo " -{$opt['short']} --{$opt['long']}\t{$opt['comm']}" . ($opt['type'] === 'required' ? "\t*required" : '') . PHP_EOL;
        }

        die();
    }

    private function buildOpts(): Collection
    {
        $shortOpts = '';
        $longOpts = [];
        foreach (self::OPTS as $opt) {
            $type = match ($opt['type']) {
                'required' => ':',
                'optionnal' => '::',
                default => '',
            };

            $shortOpts .= "{$opt['short']}{$type}";
            $longOpts[] = "{$opt['long']}{$type}";
        }

        $opts = getopt($shortOpts, $longOpts);
        foreach (self::OPTS as $opt) {
            if (isset($opts[$opt['short']])) {
                $opts[$opt['long']] = $opts[$opt['short']];
                unset($opts[$opt['short']]);
            }
        }

        return new Collection($opts);
    }

    private function getInput(): LazyCollection
    {
        try {
            return new LazyCollection(function () {
                $fp = fopen('php://stdin', 'r');
                while ($line = fgets($fp)) {
                    yield $line;
                }

                fclose($fp);
            });
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->usage();
        }
    }
}
