<?php
namespace App\Y2023\D5\P1;

use App\Challenge as BaseChallenge;
use Illuminate\Support\Collection;

class Challenge extends BaseChallenge
{
    public function resolve(): string
    {
        $input = $this->input->filter()->collect();

        $seeds = new Collection(explode(' ', substr($input->shift(), 7)));

        $maps = [
            'seed-to-soil' => new Collection(),
            'soil-to-fertilizer' => new Collection(),
            'fertilizer-to-water' => new Collection(),
            'water-to-light' => new Collection(),
            'light-to-temperature' => new Collection(),
            'temperature-to-humidity' => new Collection(),
            'humidity-to-location' => new Collection(),
        ];

        $map = '';
        foreach ($input as $row) {
            if (strpos($row, 'map') !== false) {
                $map = substr($row, 0, -5);
                echo "Map [{$map}]" . PHP_EOL;

                continue;
            }

            list($destination, $source, $length) = explode(' ', $row);
            $maps[$map]->push((object) ['source' => $source, 'destination' => $destination, 'length' => $length]);
        }

        $maps = new Collection($maps);

        $seeds = $seeds
            ->mapWithKeys(fn($seed) => [$seed => $seed])
            ->map(fn($seed) => $maps->reduce(
                function ($source, $map, $mapName) {
                    $range = $map->first(fn($range) => $range->source <= $source && $source <= $range->source + $range->length);

                    if ($range === null) {
                        echo "No range found for source {$source} in map {$mapName}" . PHP_EOL;

                        return $source;
                    }

                    echo "{$source} : Found range {$range->source} {$range->destination} {$range->length} in map {$mapName}" . PHP_EOL;
                    echo "{$source} destination : " . ($range->destination + $source - $range->source) . PHP_EOL;

                    return $range->destination + $source - $range->source;
                },
                $seed
            ));

        print_r($seeds->toArray());

        return $seeds->min();
    }
}
