<?php
namespace App;

use App\Logger;
use App\Output;
use Dotenv\Dotenv;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use stdClass as StdClass;

final class App
{
    const OPTS = [
        ['short' => 'h', 'long' => 'help', 'type' => 'no_value', 'comm' => 'Print help'],
        ['short' => 'l', 'long' => 'list', 'type' => 'no_value', 'comm' => 'List challenges'],
        ['short' => 'c', 'long' => 'create', 'type' => 'no_value', 'comm' => 'Create challenge class'],
        ['short' => 's', 'long' => 'statement', 'type' => 'no_value', 'comm' => 'Retrieve and print challenge statement'],
        ['short' => 'd', 'long' => 'debug', 'type' => 'no_value', 'comm' => 'Print debug output'],
    ];

    private Collection $opts;
    private Dotenv $dotenv;

    public function __construct(Dotenv $dotenv)
    {
        $this->opts = $this->buildOpts();
        $this->dotenv = $dotenv;
        $this->logger = new Logger();
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

                case 'statement':
                    return $this->statement($challengeArg);
            }
        }

        list($year, $day, $part) = $challengeArg !== null
            ? explode('/', $challengeArg)
            : array_values((array) $this->getChallengeList()->sort()->last());

        $challengeClass = "\\App\\Y{$year}\\D{$day}\\P{$part}\\Challenge";
        if (!class_exists($challengeClass)) {
            $this->logger()->critical("Unknown challenge {$challengeClass}");

            return $this->usage();
        }

        $input = posix_isatty(STDIN)
            ? $this->getLocalInput($year, $day)
            : 'php://stdin';

        $challenge = new $challengeClass($this->getInput($input));
        if ($this->opts->has('debug')) {
            $challenge->setLogger($this->logger);
        }

        $this->logger->notice("Challenge {$year}/{$day}/{$part} :");
        $time = microtime(true);
        $this->logger->notice($challenge->resolve());
        $this->logger->notice('Solved in ' . (microtime(true) - $time) . 's');
    }

    private function create(string $challengeArg = null): void
    {
        list($year, $day, $part) = $challengeArg !== null
            ? explode('/', $challengeArg)
            : array_values((array) $this->getNextChallenge());

        $this->logger->notice("create {$year}/{$day}/{$part}");

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
        $this->logger->notice("New file created : {$filename}");
    }

    private function list(): void
    {
        $this->logger->notice('list :');
        foreach ($this->getChallengeList() as $info) {
            $this->logger->notice("{$info->year}/{$info->day}/{$info->part}");
        }
    }

    private function statement(string $challengeArg = null): void
    {
        list($year, $day, $part) = $challengeArg !== null
            ? explode('/', $challengeArg)
            : array_values((array) $this->getChallengeList()->sort()->last());

        $this->dotenv->required('AOC_SESSION')->notEmpty();
        $session = $_ENV['AOC_SESSION'];
        $contents = file_get_contents("https://adventofcode.com/{$year}/day/{$day}", false, stream_context_create([
            'http' => ['header' => "Cookie: session={$session}\r\n"],
        ]));

        $start = strpos($contents, '<article');
        $end = strrpos($contents, '</article>');
        $contents = substr($contents, $start, $end - $start);

        $output = new Output();
        $replacements = [
            '#<pre>(.*?)</pre>(*SKIP)(*FAIL)|\n#s' => '',
            '#<em>(.*?)</em>#' => $output->whiteBold('$1'),
            '#<li>(.*?)</li>#' => ' - $1' . PHP_EOL,
            '#<p>(.*?)</p>#' => '$1' . PHP_EOL . PHP_EOL,
            '#<h2[^>]*>(.*?)</h2>#' => $output->whiteBold('$1') . PHP_EOL . PHP_EOL,
            '#\n</code>#s' => '</code>',
            '#<code>(.*?)</code>#s' => $output->bgGray('$1'),
            '#<pre>(.*?)</pre>#s' => $output->bgGray('$1') . "\033[K" . PHP_EOL . PHP_EOL,
        ];

        $contents = preg_replace(array_keys($replacements), array_values($replacements), $contents);

        $this->logger->notice('');
        $this->logger->notice(strip_tags($contents));
    }

    private function usage(): void
    {
        $this->logger->notice('Usage :');
        $this->logger->notice('php scripts.php [OPTIONS] [challenge] < input');
        $this->logger->notice(" [challenge]\tChallenge with format : <year>/<day>/<part>");
        foreach (self::OPTS as $opt) {
            $this->logger->notice(" -{$opt['short']} --{$opt['long']}\t{$opt['comm']}" . ($opt['type'] === 'required' ? "\t*required" : ''));
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

    private function getLocalInput(int $year, int $day): string
    {
        if (!is_dir(__DIR__ . '/../inputs')) {
            mkdir(__DIR__ . '/../inputs');
        }

        $filename = __DIR__ . "/../inputs/{$year}-{$day}.txt";
        if (!file_exists($filename)) {
            $this->dotenv->required('AOC_SESSION')->notEmpty();
            $session = $_ENV['AOC_SESSION'];
            $this->logger->notice("Downloading input...");
            $contents = file_get_contents("https://adventofcode.com/{$year}/day/{$day}/input", false, stream_context_create([
                'http' => ['header' => "Cookie: session={$session}\r\n"],
            ]));

            file_put_contents($filename, $contents);
        }

        return $filename;
    }

    private function getInput(string $from): LazyCollection
    {
        return new LazyCollection(function () use ($from) {
            $fp = fopen($from, 'r');
            while ($line = fgets($fp)) {
                yield str_replace(["\r", "\n"], '', $line);
            }

            fclose($fp);
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
}
