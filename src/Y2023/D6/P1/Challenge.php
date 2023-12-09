<?php
namespace App\Y2023\D6\P1;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        return $this->getRaces()
            ->map(function ($race) {
                $halfTime = $race->time / 2;
                $time = ceil($halfTime);

                while ($this->getDistance($time, $race->time) > $race->distance) {
                    $time++;
                }

                return ($time - $halfTime - 1) * 2 + ($halfTime === ceil($halfTime) ? 0 : 1);
            })
            ->reduce(fn($margin, $distance) => $margin * $distance, 1);
    }

    private function getDistance(int $holdTime, int $raceTime): int
    {
        return $holdTime * ($raceTime - $holdTime);
    }

    private function getRaces(): Collection
    {
        $input = $this->input
            ->mapWithKeys(function ($row) {
                list($key, $values) = explode(':', $row);
                $values = new Collection(explode(' ', $values));

                return [$key => $values->filter()];
            })
            ->toArray();

        $races = new Collection(array_combine($input['Time'], $input['Distance']));

        return $races
            ->map(fn ($distance, $time) => (object) ['time' => $time, 'distance' => $distance])
            ->values();
    }
}
