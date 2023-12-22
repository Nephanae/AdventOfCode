<?php
namespace App\Y2023\D13\P1;

use App\ChallengeAbstract;
use App\Map;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $maps = $this->input
            ->chunkWhile(fn ($value) => !empty($value))
            ->collect()
            ->map(fn ($map) => new Map($map->filter()->values()));

        return $maps->map(function ($map) {
            // $this->logger->debug("{$map->size->x},{$map->size->y}");
            $rows = $map->getRows()->map(fn ($row) => $row->map(fn ($tile) => (string) $tile)->implode(''))->collect();
            $cols = $map->getCols()->map(fn ($col) => $col->map(fn ($tile) => (string) $tile)->implode(''))->collect();

            $reflexionCol = $this->getReflexionScore($cols);
            $reflexionRow = $this->getReflexionScore($rows);

            $this->logger->debug($reflexionCol > 0 ? ' ' . str_repeat(' ', $reflexionCol - 1) . '><' : '');
            $states = $reflexionRow > 0 ? [$reflexionRow - 1 => 'v', $reflexionRow => '^'] : [];
            foreach ($rows as $index => $row) {
                $state = $states[$index] ?? ' ';
                $this->logger->debug("{$state}{$row}");
            }

            $score = $reflexionCol + 100 * $reflexionRow;
            $this->logger->debug($score);

            return $score;
        })
        ->sum();
    }

    private function getReflexionScore(Collection $rows): int
    {
        foreach ($rows->duplicates() as $pos => $row) {
            if ($this->hasReflexion($rows, $pos)) {
                return $pos;
            }
        }

        return 0;
    }

    private function hasReflexion(Collection $rows, int $pos): bool
    {
        for ($i = 0; $i < $pos; $i++) {
            $right = $pos + $i;
            $left = $pos - 1 - $i;

            if (!$rows->has($right)) {
                return true;
            }

            if ($rows->get($left) !== $rows->get($right)) {
                return false;
            }
        }

        return true;
    }
}
