<?php
namespace App\Y2023\D4\P1;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        return $this->input
            ->map(fn($row) => explode(':', $row))
            ->map(fn($row) => explode('|', $row[1]))
            ->map(fn($row) => (object) [
                'winning' => new Collection(explode(' ', trim($row[0]))),
                'cards' => new Collection(explode(' ', trim($row[1]))),
            ])
            ->map(fn($row) => $row->winning->filter(fn($card) => $row->cards->contains($card))->filter()->count())
            ->map(fn($count) => $count < 2 ? $count : pow(2, $count - 1))
            ->sum();
    }
}
