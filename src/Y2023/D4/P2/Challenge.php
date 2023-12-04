<?php
namespace App\Y2023\D4\P2;

use App\Challenge as BaseChallenge;
use Illuminate\Support\Collection;

class Challenge extends BaseChallenge
{
    public function resolve(): string
    {
        $input = $this->input
            ->map(fn($row) => explode(':', $row))
            ->mapWithKeys(fn($row) => [trim(substr($row[0], 5)) => explode('|', $row[1])])
            ->collect();

        $cardsWinningNumber = $input
            ->map(fn($row) => (object) [
                'winning' => new Collection(explode(' ', trim($row[0]))),
                'cards' => new Collection(explode(' ', trim($row[1]))),
            ])
            ->map(fn($row) => $row->winning->filter(fn($card) => $row->cards->contains($card))->filter()->count());

        $wonCards = $input->map(fn($row) => 1)->toArray();

        foreach ($cardsWinningNumber as $key => $value) {
            $nbCards = $wonCards[$key];
            for ($card = $key + 1; $card < $key + $value + 1; $card++) {
                $wonCards[$card] += $nbCards;
            }
        }
        
        return array_sum($wonCards);
    }
}
