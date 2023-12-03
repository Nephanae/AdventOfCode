<?php
namespace App\Y2023\D3\P1;

use App\Challenge as BaseChallenge;
use Illuminate\Support\Collection;
use stdClass as StdClass;

class Challenge extends BaseChallenge
{
    private array $engine;
    private array $debug;
    private Collection $numbers;

    public function resolve(): string
    {
        $this->engine = $this->input->map(fn($row) => str_split($row))->toArray();
        $this->debug = $this->engine;
        $this->numbers = new Collection();

        $rowCount = count($this->engine);
        $colCount = count($this->engine[0]);
        for ($row = 0; $row < $rowCount; $row++) {
            for ($col = 0; $col < $colCount; $col++) {
                if (is_numeric($this->engine[$row][$col])) {
                    $number = (object) [
                        'number' => $this->extractNumber($row, $col),
                        'row' => $row,
                        'col' => $col,
                    ];

                    foreach (str_split($number->number) as $key => $char) {
                        $this->debug[$number->row][$number->col + $key] = $this->output->red($char);
                    }

                    if ($this->isPartNumber($number)) {
                        $this->numbers->push($number);
                        foreach (str_split($number->number) as $key => $char) {
                            $this->debug[$number->row][$number->col + $key] = $this->output->green($char);
                        }
                    }

                    $col += strlen($number->number);
                }
            }
        }

        $this->debug();

        return $this->numbers->sum('number');
    }

    public function debug()
    {
        foreach ($this->debug as $row => $line) {
            echo implode('', $line) . PHP_EOL;
        }
    }

    private function extractNumber(int $row, int $col): int
    {
        $number = '';
        while (isset($this->engine[$row][$col]) && is_numeric($this->engine[$row][$col])) {
            $number .= $this->engine[$row][$col];
            $col++;
        }

        return $number;
    }

    private function isPartNumber(StdClass $number): bool
    {
        $rowStart = $number->row - 1;
        $rowEnd = $number->row + 2;
        $colStart = $number->col - 1;
        $colEnd = $number->col + strlen($number->number) + 1;

        echo "Found number {$number->number} at {$number->col},{$number->row}" . PHP_EOL;
        echo "Searching from {$colStart},{$rowStart} to {$colEnd},{$rowEnd}" . PHP_EOL;
        
        $found = false;
        for ($row = $rowStart; $row < $rowEnd; $row++) {
            for ($col = $colStart; $col < $colEnd; $col++) {
                // echo "Searching at {$col},{$row}: " . ($this->engine[$row][$col] ?? 'NULL') . PHP_EOL;
                if (
                    isset($this->engine[$row])
                    && isset($this->engine[$row][$col])
                    && !is_numeric($this->engine[$row][$col])
                    && $this->engine[$row][$col] !== '.'
                ) {
                    $this->debug[$row][$col] = $this->output->blue($this->engine[$row][$col]);
                    echo $this->output->green($this->engine[$row][$col]);

                    $found = true;

                    continue;
                }

                if (isset($this->engine[$row]) && isset($this->engine[$row][$col])) {
                    echo $this->output->red($this->engine[$row][$col]);
                }
            }

            echo PHP_EOL;
        }

        return $found;
    }
}
