<?php
namespace App\Y2023\D5\P2;

use App\Challenge as BaseChallenge;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use stdClass as StdClass;

class Challenge extends BaseChallenge
{
    public function resolve(): string
    {
        $input = $this->computeInput();

        return $this->getLocations($input->seeds, $input->maps)
            ->map(fn($range) => $range->start)
            ->min();
    }

    private function computeInput(): StdClass
    {
        $input = $this->input->filter()->collect();

        $seeds = new Collection(explode(' ', substr($input->shift(), 7)));

        $seeds = $seeds
            ->chunk(2)
            ->map(fn ($chunk) => (object) ['start' => $chunk->first(), 'end' => $chunk->first() + $chunk->last() - 1]);

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

                continue;
            }

            list($destination, $source, $length) = explode(' ', $row);
            $maps[$map]->push((object) ['source' => (object) ['start' => $source, 'end' => $source + $length - 1], 'destination' => (object) ['start' => $destination, 'end' => $destination + $length - 1]]);
        }

        $maps = new Collection($maps);

        return (object) [
            'seeds' => $seeds,
            'maps' => $maps->map(fn($map) => $map->sortBy(fn($mapRange) => $mapRange->source->start)),
        ];
    }

    private function getLocations(Collection $seeds, Collection $maps): LazyCollection
    {
        return new LazyCollection(function () use ($seeds, $maps) {
            foreach ($seeds as $seed) {
                foreach ($this->getDestinationRanges($seed, $maps->get('seed-to-soil')) as $soil) {
                    foreach ($this->getDestinationRanges($soil, $maps->get('soil-to-fertilizer')) as $fertilizer) {
                        foreach ($this->getDestinationRanges($fertilizer, $maps->get('fertilizer-to-water')) as $water) {
                            foreach ($this->getDestinationRanges($water, $maps->get('water-to-light')) as $light) {
                                foreach ($this->getDestinationRanges($light, $maps->get('light-to-temperature')) as $temperature) {
                                    foreach ($this->getDestinationRanges($temperature, $maps->get('temperature-to-humidity')) as $humidity) {
                                        foreach ($this->getDestinationRanges($humidity, $maps->get('humidity-to-location')) as $location) {
                                            yield $location;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    private function getDestinationRanges(stdClass $sourceRange, Collection $map): LazyCollection
    {
        return new LazyCollection(function () use ($sourceRange, $map) {
            $mapRange = $map->first(fn($mapRange) => $this->overlap($sourceRange, $mapRange->source));

            // Destination = Source
            if ($mapRange === null) {
                yield $sourceRange;

                return;
            }

            // Destination = Source
            if ($sourceRange->start < $mapRange->source->start) {
                yield (object) ['start' => $sourceRange->start, 'end' => $mapRange->source->start - 1];
            }

            yield (object) [
                'start' => $mapRange->destination->start - $mapRange->source->start + max($sourceRange->start, $mapRange->source->start),
                'end' => $mapRange->destination->end - $mapRange->source->end + min($sourceRange->end, $mapRange->source->end),
            ];

            if ($sourceRange->end <= $mapRange->source->end) {
                return;
            }

            yield from $this->getDestinationRanges((object) ['start' => $mapRange->source->end + 1, 'end' => $sourceRange->end], $map);
        });
    }

    private function overlap(StdClass $range1, StdClass $range2): bool
    {
        return !($range1->start > $range2->end || $range1->end < $range2->start);
    }
}

