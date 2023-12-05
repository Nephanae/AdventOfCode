<?php
namespace App;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use stdClass as StdClass;

final class App
{
    const OPTS = [
        ['short' => 'h', 'long' => 'help', 'type' => 'no_value', 'comm' => 'Print help'],
        ['short' => 'l', 'long' => 'list', 'type' => 'no_value', 'comm' => 'List challenges'],
        ['short' => 'c', 'long' => 'create', 'type' => 'optional', 'comm' => 'Create challenge class'],
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

    public function run(array $argv)
    {
        if ($this->opts->has('help')) {
            return $this->usage();
        }

        $args = array_filter($argv, fn($arg) => substr_count($arg, '/') === 2);
        $challengeArg = count($args) === 1 ? current($args) : null;

        foreach ($this->opts->keys() as $opt) {
            switch ($opt) {
                case 'list':
                    return $this->list();

                case 'create':
                    return $this->create($challengeArg);
            }
        }

        list($year, $day, $part) = $challengeArg !== null
            ? explode('/', $challengeArg)
            : array_values((array) $this->getChallengeList()->sort()->last());

        $challengeClass = "\\App\\Y{$year}\\D{$day}\\P{$part}\\Challenge";
        if (!class_exists($challengeClass)) {
            echo "Unknown challenge {$challengeClass}" . PHP_EOL;

            return $this->usage();
        }
        
        $challenge = new $challengeClass($this->getInput());

        echo "Challenge {$year}/{$day}/{$part} :" . PHP_EOL;
        echo $challenge->resolve() . PHP_EOL;
    }

    private function create(array $challengeArg = null): void
    {
        list($year, $day, $part) = $challengeArg !== null
            ? explode('/', $challengeArg)
            : array_values((array) $this->getNextChallenge());

        echo "create {$year}/{$day}/{$part}" . PHP_EOL;

        if (!is_dir(__DIR__ . "/Y{$year}")) {
            mkdir(__DIR__ . "/Y{$year}");
        }

        if (!is_dir(__DIR__ . "/Y{$year}/D{$day}")) {
            mkdir(__DIR__ . "/Y{$year}/D{$day}");
        }

        mkdir(__DIR__ . "/Y{$year}/D{$day}/P{$part}");

        $filename = __DIR__ . "/Y{$year}/D{$day}/P{$part}/Challenge.php";

        ob_start();
        include __DIR__ . '/ChallengeTemplate.php';
        $fileContents = ob_get_clean();

        file_put_contents($filename, '<?php' . PHP_EOL . $fileContents);
        echo "New file created : {$filename}" . PHP_EOL;
    }

    private function getNextChallenge(): StdClass
    {
        $lastChallenge = $this->getChallengeList()->sort()->last();
        if ($lastChallenge->day === '25' && $lastChallenge->part === '2') {
            return (object) ['year' => $lastChallenge->year + 1, 'day' => '1', 'part' => '1'];
        }

        if ($lastChallenge->part === '1') {
            return (object) ['year' => $lastChallenge->year, 'day' => $lastChallenge->day, 'part' => '2'];
        }

        return (object) ['year' => $lastChallenge->year, 'day' => $lastChallenge->day + 1, 'part' => '1'];
    }

    private function list(): void
    {
        echo 'list :' . PHP_EOL;
        foreach ($this->getChallengeList() as $info) {
            echo "{$info->year}/{$info->day}/{$info->part}" . PHP_EOL;
        }
    }

    private function usage(): void
    {
        echo 'Usage :' . PHP_EOL;
        echo 'php scripts.php [OPTIONS] [challenge] < input' . PHP_EOL;
        echo " [challenge]\tChallenge with format : <year>/<day>/<part>" . PHP_EOL;
        foreach (self::OPTS as $opt) {
            echo " -{$opt['short']} --{$opt['long']}\t{$opt['comm']}" . ($opt['type'] === 'required' ? "\t*required" : '') . PHP_EOL;
        }
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
