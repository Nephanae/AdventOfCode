<?php
namespace App\Y2023\D6\P2;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;
use stdClass as StdClass;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $race = $this->getRace();

        $halfTime = $race->time / 2;
        $time = ceil($halfTime);

        while ($this->getDistance($time, $race->time) > $race->distance) {
            $time++;
        }

        return ($time - $halfTime - 1) * 2 + ($halfTime === ceil($halfTime) ? 0 : 1);
    }

    private function getDistance(int $holdTime, int $raceTime): int
    {
        return $holdTime * ($raceTime - $holdTime);
    }

    private function getRace(): StdClass
    {
        $input = $this->input
            ->mapWithKeys(function ($row) {
                list($key, $values) = explode(':', $row);
                $values = new Collection(explode(' ', $values));

                return [$key => $values->filter()->implode('')];
            })
            ->toArray();

        return (object) ['time' => $input['Time'], 'distance' => $input['Distance']];
    }
}
