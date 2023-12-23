<?php
namespace App\Y2023\D16\P2;

use App\ChallengeAbstract;
use App\Map;
use App\Y2023\D16\Tile;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $map = new Map($this->input, Tile::class);

        return $this->getBeamStarts($map)
            ->map(function ($beam) use ($map) {
                // Reset energized
                foreach ($map->getTiles() as $tile) {
                    $tile->energized = [];
                }

                $log = "{$beam->x},{$beam->y} {$beam->direction->x},{$beam->direction->y}";
                $this->logger->info($log);

                $this->runBeams($map, new Collection([$beam]));

                $energized = $map->getTiles()->filter(fn ($tile) => !empty($tile->energized))->count();

                $this->logger->debug("{$log} => {$energized}");

                return $energized;
            })
            ->max();
    }

    private function getBeamStarts(Map $map): LazyCollection
    {
        return new LazyCollection(function () use ($map) {
            for ($x = 0; $x < $map->size->x; $x++) {
                yield (object) [
                    'x' => $x,
                    'y' => -1,
                    'direction' => (object) ['x' => 0, 'y' => 1],
                ];

                yield (object) [
                    'x' => $x,
                    'y' => $map->size->y,
                    'direction' => (object) ['x' => 0, 'y' => -1],
                ];
            }

            for ($y = 0; $y < $map->size->y; $y++) {
                yield (object) [
                    'x' => -1,
                    'y' => $y,
                    'direction' => (object) ['x' => 1, 'y' => 0],
                ];

                yield (object) [
                    'x' => $map->size->x,
                    'y' => $y,
                    'direction' => (object) ['x' => -1, 'y' => 0],
                ];
            }
        });
    }

    private function runBeams(Map $map, Collection $beams)
    {
        while ($beams->isNotEmpty()) {
            $beam = $beams->shift();

            while ($map->has($beam->x + $beam->direction->x, $beam->y + $beam->direction->y)) {
                $beam->x += $beam->direction->x;
                $beam->y += $beam->direction->y;

                $tile = $map->get($beam->x, $beam->y);

                if ($this->directionInEnergized($beam->direction, $tile->energized)) {
                    break;
                }

                $tile->energized[] = (object) ['x' => $beam->direction->x, 'y' => $beam->direction->y];

                switch ((string) $tile) {
                    case '.':
                        break;

                    case '/':
                        $beam->direction = (object) ['x' => $beam->direction->y * -1, 'y' => $beam->direction->x * -1];
                        break;

                    case '\\':
                        $beam->direction = (object) ['x' => $beam->direction->y, 'y' => $beam->direction->x];
                        break;

                    case '|':
                        if ($beam->direction->x !== 0) {
                            $beam->direction = (object) ['x' => $beam->direction->y, 'y' => $beam->direction->x];
                            $beams->push((object) [
                                'x' => $beam->x,
                                'y' => $beam->y,
                                'direction' => (object) ['x' => 0, 'y' => $beam->direction->y * -1],
                            ]);
                        }
                        break;

                    case '-':
                        if ($beam->direction->y !== 0) {
                            $beam->direction = (object) ['x' => $beam->direction->y, 'y' => $beam->direction->x];
                            $beams->push((object) [
                                'x' => $beam->x,
                                'y' => $beam->y,
                                'direction' => (object) ['x' => $beam->direction->x * -1, 'y' => 0],
                            ]);
                        }
                        break;
                }
            }
        }
    }

    private function directionInEnergized(object $direction, array $energized): bool
    {
        foreach ($energized as $energizedDirection) {
            if ($energizedDirection->x === $direction->x && $energizedDirection->y === $direction->y) {
                return true;
            }
        }

        return false;
    }
}
