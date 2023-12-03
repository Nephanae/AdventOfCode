<?php
namespace App\Y2023\D2\P2;

use App\Challenge as BaseChallenge;
use Illuminate\Support\Collection;

class Challenge extends BaseChallenge
{
    public function resolve(): string
    {
        $cubes = new Collection(['red', 'green', 'blue']);

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
            ->map(
                fn($turns) => $cubes
                    ->mapWithKeys(fn($color) => [$color => $turns->max($color)])->filter()
                    ->reduce(fn($power, $color) => $power *= $color, 1)
            )
            ->sum();
    }
}

