<?php
namespace App\Y2023\D7\P2;

use App\Challenge as BaseChallenge;
use Illuminate\Support\Collection;

class Challenge extends BaseChallenge
{
    const CARDS = ['J' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, 'T' => 10, 'Q' => 11, 'K' => 12, 'A' => 13];
    const HIGH_CARD = 1;
    const ONE_PAIR = 2;
    const TWO_PAIR = 3;
    const THREE_OF_A_KIND = 4;
    const FULL_HOUSE = 5;
    const FOUR_OF_A_KIND = 6;
    const FIVE_OF_A_KIND = 7;

    public function resolve(): string
    {
        return $this->input
            ->map(function ($row) {
                list($hand, $bid) = explode(' ', $row);

                $hand = new Collection(str_split($hand));

                return (object) ['hand' => $hand, 'bid' => $bid, 'type' => $this->getHandType($hand)];
            })
            ->sort(function ($a, $b) {
                if ($a->type !== $b->type) {
                    return $a->type < $b->type ? -1 : 1;
                }

                foreach ($a->hand as $key => $cardA) {
                    $cardB = $b->hand->get($key);
                    if ($cardA !== $cardB) {
                        return self::CARDS[$cardA] < self::CARDS[$cardB] ? -1 : 1;
                    }
                }

                return 0;
            })
            ->values()
            ->map(fn($hand, $key) => $hand->bid * ($key + 1))
            ->sum();
    }

    public function getHandType(Collection $hand): string
    {
        $duplicates = $hand->filter(fn($card) => $card !== 'J')->countBy()->sortDesc()->values();
        $jokers = $hand->filter(fn($card) => $card === 'J')->count();
        $duplicates->put(0, $duplicates->get(0) + $jokers);

        if ($duplicates->contains(5)) {
            return self::FIVE_OF_A_KIND;
        }

        if ($duplicates->contains(4)) {
            return self::FOUR_OF_A_KIND;
        }

        if ($duplicates->contains(3) && $duplicates->contains(2)) {
            return self::FULL_HOUSE;
        }

        if ($duplicates->contains(3)) {
            return self::THREE_OF_A_KIND;
        }

        if ($duplicates->contains(2)) {
            return $duplicates->countBy()->get(2) === 2 ? self::TWO_PAIR : self::ONE_PAIR;
        }

        return self::HIGH_CARD;
    }
}
