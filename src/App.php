<?php
namespace App;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

final class App
{
    const OPTS = [
        ['short' => 'h', 'long' => 'help', 'type' => 'no_value', 'comm' => 'Print help'],
        ['short' => 'l', 'long' => 'list', 'type' => 'no_value', 'comm' => 'List challenges'],
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

    public function run(array $argv): void
    {
        if ($this->opts->has('help')) {
            $this->usage();
        }

        if ($this->opts->has('list')) {
            foreach ($this->getChallengeList() as $info) {
                echo "{$info->year}/{$info->day}/{$info->part}" . PHP_EOL;
            }

            die();
        }

        $args = array_filter($argv, fn($arg) => substr_count($arg, '/') === 2);

        if (count($args) !== 1) {
            $this->usage();
        }

        list($year, $day, $part) = explode('/', current($args));

        $challengeClass = "\\App\\Y{$year}\\D{$day}\\P{$part}\\Challenge";
        if (!class_exists($challengeClass)) {
            echo "Unknown challenge {$challengeClass}" . PHP_EOL;
            $this->usage();
        }
        
        $challenge = new $challengeClass($this->getInput());

        echo $challenge->resolve() . PHP_EOL;
    }

    public function usage(): void
    {
        echo 'Usage :' . PHP_EOL;
        echo 'php scripts.php [OPTIONS] <challenge> < input' . PHP_EOL;
        echo " <challenge>\tChallenge with format : <year>/<day>/<part>" . PHP_EOL;
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
                    yield str_replace(["\r", "\n"], '', $line);
                }

                fclose($fp);
            });
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->usage();
        }
    }

    private function getChallengeList(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getYearList() as $year) {
                foreach ($this->getDayList($year) as $day) {
                    foreach ($this->getPartList($year, $day) as $part) {
                        $challengeClass = "\\App\\Y{$year}\\D{$day}\\P{$part}\\Challenge";
                        if (class_exists($challengeClass)) {
                            yield (object) ['year' => $year, 'day' => $day, 'part' => $part];
                        }
                    }
                }
            }
        });
    }

    private function getChallengeDir(string $root, string $letter): LazyCollection
    {
        return new LazyCollection(function () use ($root, $letter) {
            $files = scandir($root);
            sort($files);
            foreach ($files as $file) {
                if (substr($file, 0, 1) === $letter && is_dir("{$root}/{$file}")) {
                    yield substr($file, 1);

                }
            }
        });
    }

    private function getYearList(): LazyCollection
    {
        return $this->getChallengeDir(__DIR__, 'Y');
    }

    private function getDayList(int $year): LazyCollection
    {
        return $this->getChallengeDir(__DIR__ . "/Y{$year}", 'D');
    }

    private function getPartList(int $year, int $day): LazyCollection
    {
        return $this->getChallengeDir(__DIR__ . "/Y{$year}/D${day}", 'P');
    }
}
