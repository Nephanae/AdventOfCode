<?php
namespace App\Y2023\D2\P1;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    const CUBES = [
        'red' => 12,
        'green' => 13,
        'blue' => 14,
    ];

    public function resolve(): string
    {
        return $this->input
            ->map(fn($row) => explode(':', $row))
            ->mapWithKeys(fn($row) => [substr($row[0], 5) => new Collection(explode(';', $row[1]))])
            ->map(
                fn($turns) => $turns
                    ->map(fn($turn) => new Collection(explode(',', $turn)))
                    ->map(
                        fn($turn) => $turn
                            ->map(fn($set) => explode(' ', trim($set)))
                            ->mapWithKeys(fn($set) => [$set[1] => $set[0]])
                    )
            )
            ->filter(fn($game) => $game->every(fn($sets) => $sets->every(fn($count, $color) => $count <= self::CUBES[$color])))
            ->keys()
            ->sum();
    }
}

