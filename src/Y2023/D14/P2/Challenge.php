<?php
namespace App\Y2023\D14\P2;

use App\ChallengeAbstract;
use App\Y2023\D14\P2\Map;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $map = new Map($this->input);
        $map->setLogger($this->logger);

        foreach ($map->getRows() as $row) {
            $this->logger->debug($row->map(fn ($tile) => (string) $tile)->implode(''));
        }

        $this->logger->debug('');

        $weights = [];
        $pattern = null;
        for ($i = 0; $i < 1000000000; $i++) {
            $map->spinCycle();

            $weight = 0;
            foreach ($map->getRows() as $y => $row) {
                $roundedRocksCount = $row->filter(fn ($tile) => (string) $tile === 'O')->count();
                $rowWeight = $map->size->y - $y;
                $roundedRocksWeight = $rowWeight * $roundedRocksCount;
                $weight += $roundedRocksWeight;
            }

            if ($pattern !== null && $pattern->array[$pattern->index] !== $weight) {
                $this->logger->debug('Reset pattern');
                $pattern = null;
            }

            if ($pattern === null && ($index = array_search($weight, $weights)) !== false) {
                $pattern = array_slice($weights, $index);
                $this->logger->debug("Found pattern at {$index} : " . implode(' ', $pattern));

                $pattern = (object) [
                    'array' => $pattern,
                    'start' => $index,
                    'index' => 0,
                    'count' => count($pattern),
                    'log' => '',
                    'checked' => 0,
                ];
            }

            $weights[] = $weight;

            if ($pattern !== null && $pattern->array[$pattern->index] === $weight) {
                $pattern->array[$pattern->index] = $this->output->blue($weight);
                $pattern->log = implode(' ', $pattern->array);
                $pattern->array[$pattern->index] = $weight;

                $pattern->index++;
                if ($pattern->index === $pattern->count) {
                    $pattern->index = 0;
                    $pattern->checked++;
                }
            } 

            $this->logger->debug("Cycle {$i} : {$weight} " . ($pattern !== null ? $pattern->log : ''));

            if ($pattern !== null && $pattern->checked > 2) {
                break;
            }
        }

        $fullPatternCount = floor((1000000000 - $pattern->start) / $pattern->count);
        $partialPatternCount = 1000000000 - $pattern->start - ($fullPatternCount * $pattern->count);

        $this->logger->debug("start = {$pattern->start}");
        $this->logger->debug("full patterns count = {$fullPatternCount}");
        $this->logger->debug("partial patterns count = {$partialPatternCount}");

        return $pattern->array[$partialPatternCount - 1];
    }
}
