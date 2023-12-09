<?php
namespace App\Y2023\D1\P1;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        return $this->input
            ->filter()
            ->map(fn($row) => new Collection(str_split($row)))
            ->map(fn($row) => $row->filter(fn($char) => is_numeric($char)))
            ->map(fn($row) => $row->first() . $row->last())
            ->sum();
    }
}
