<?php
namespace App\Y2023\D3\P2;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;
use stdClass as StdClass;

class Challenge extends ChallengeAbstract
{
    private array $engine;
    private array $debug;
    private Collection $numbers;

    public function resolve(): string
    {
        $this->engine = $this->input->map(fn($row) => str_split($row))->toArray();
        $this->debug = $this->engine;
        $this->gears = new Collection();

        $rowCount = count($this->engine);
        $colCount = count($this->engine[0]);
        for ($row = 0; $row < $rowCount; $row++) {
            for ($col = 0; $col < $colCount; $col++) {
                if ($this->engine[$row][$col] !== '*') {
                    continue;
                }

                $numbers = $this->extractNumbers($row, $col);
                if ($numbers->count() !== 2) {
                    $this->debug[$row][$col] = $this->output->red('*');

                    continue;
                }

                echo "Found gear at {$col},{$row}" . PHP_EOL;
                $this->debug[$row][$col] = $this->output->green('*');
                foreach ($numbers as $number) {
                    echo "Found gear number {$number->number} from {$number->colStart},{$number->row} to {$number->colEnd},{$number->row}" . PHP_EOL;
                    for ($numberCol = $number->colStart; $numberCol < $number->colEnd + 1; $numberCol++) {
                        $this->debug[$number->row][$numberCol] = $this->output->blue($this->engine[$number->row][$numberCol]);
                    }
                }

                $this->gears->push($numbers->reduce(fn($ratio, $number) => $ratio * $number->number, 1));
            }
        }

        $this->debug();

        return $this->gears->sum();
    }

    public function debug()
    {
        foreach ($this->debug as $row => $line) {
            echo implode('', $line) . PHP_EOL;
        }
    }

    private function extractNumbers(int $row, int $col): Collection
    {
        $numbers = new Collection();
        $rowStart = $row - 1;
        $rowEnd = $row + 2;
        $colStart = $col - 1;
        $colEnd = $col + 2;

        for ($row = $rowStart; $row < $rowEnd; $row++) {
            for ($col = $colStart; $col < $colEnd; $col++) {
                if (!is_numeric($this->engine[$row][$col])) {
                    continue;
                }

                $number = $this->extractNumber($row, $col);
                $numbers->push($number);
                $col = $number->colEnd + 1;
            }
        }

        return $numbers;
    }

    public function extractNumber(int $row, int $col): StdClass
    {
        $colStart = $colEnd = $col;
        while (is_numeric($this->engine[$row][$colStart - 1])) {
            $colStart--;
        }

        while (is_numeric($this->engine[$row][$colEnd + 1])) {
            $colEnd++;
        }

        return (object) [
            'number' => implode('', array_slice($this->engine[$row], $colStart, $colEnd - $colStart + 1)),
            'row' => $row,
            'colStart' => $colStart,
            'colEnd' => $colEnd,
        ];
    }
}
