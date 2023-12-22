<?php
namespace App\Y2023\D13\P2;

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
            $rows = $map->getRows()->map(fn ($row) => $row->map(fn ($tile) => (string) $tile)->implode(''))->collect();
            $cols = $map->getCols()->map(fn ($col) => $col->map(fn ($tile) => (string) $tile)->implode(''))->collect();

            $reflexionRow = $this->getReflexionScore($rows);
            $reflexionCol = $this->getReflexionScore($cols);

            $smudge = $this->getSmudge($rows, $reflexionRow);
            if ($smudge === null) {
                $smudge = $this->getSmudge($cols, $reflexionCol);
                list($smudge->x, $smudge->y) = [$smudge->y, $smudge->x];
            }

            $smudgeRow = $rows->get($smudge->y);
            $smudgeRow[$smudge->x] = $smudge->char;
            $rows->put($smudge->y, $smudgeRow);
            $smudgeCol = $cols->get($smudge->x);
            $smudgeCol[$smudge->y] = $smudge->char;
            $cols->put($smudge->x, $smudgeCol);

            $reflexionRow = $this->getReflexionScore($rows, $reflexionRow);
            $reflexionCol = $this->getReflexionScore($cols, $reflexionCol);

            $smudgeRow = substr($smudgeRow, 0, $smudge->x) . $this->output->bgGray($this->output->blue($smudgeRow[$smudge->x])) . substr($smudgeRow, $smudge->x + 1);
            $rows->put($smudge->y, $smudgeRow);

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

    private function getSmudge(Collection $rows, int $score): ?object
    {
        $rows = clone $rows;
        foreach ($rows as $fromY => $fromRow) {
            foreach ($rows as $toY => $toRow) {
                if ($fromY === $toY) {
                    continue;
                }

                $smudgeX = $this->getSmudgeCandidate($fromRow, $toRow);
                if ($smudgeX === null) {
                    continue;
                }

                $fromRow[$smudgeX] = $fromRow[$smudgeX] === '.' ? '#' : '.';
                $rows->put($fromY, $fromRow);

                $newScore = $this->getReflexionScore($rows, $score);
                if ($newScore > 0 && $newScore !== $score) {
                    return (object) ['x' => $smudgeX, 'y' => $fromY, 'char' => $fromRow[$smudgeX]];
                }

                $fromRow[$smudgeX] = $fromRow[$smudgeX] === '.' ? '#' : '.';
                $rows->put($fromY, $fromRow);
            } 
        }

        return null;
    }

    private function getSmudgeCandidate(string $fromRow, string $toRow): ?int
    {
        $smudge = null;
        $length = strlen($fromRow);
        for ($i = 0; $i < $length; $i++) {
            if ($fromRow[$i] === $toRow[$i]) {
                continue;
            }

            if ($smudge !== null) {
                return null;
            }

            $smudge = $i;
        }

        return $smudge;
    }

    private function getReflexionScore(Collection $rows, int $score = 0): int
    {
        foreach ($rows->duplicates() as $pos => $row) {
            if ($pos !== $score && $this->hasReflexion($rows, $pos)) {
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
