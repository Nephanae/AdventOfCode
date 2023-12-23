<?php
namespace App\Y2023\D16\P1;

use App\ChallengeAbstract;
use App\Map;
use App\Y2023\D16\Tile;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $map = new Map($this->input, Tile::class);

        $this->runBeams($map, new Collection([
            (object) [
                'x' => -1,
                'y' => 0,
                'direction' => (object) ['x' => 1, 'y' => 0],
            ],
        ]));

        foreach ($map->getRows() as $row) {
            $this->logger->debug($row->map(fn ($tile) => $tile->energized ? $this->output->blue((string) $tile): (string) $tile)->implode(''));
        }

        return $map->getTiles()->filter(fn ($tile) => !empty($tile->energized))->count();
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

                $this->logger->debug("Tile {$tile} Beam {$beam->x},{$beam->y} => {$beam->direction->x},{$beam->direction->y}");

                switch ((string) $tile) {
                    case '.':
                        break;

                    case '/':
                        $beam->direction = (object) ['x' => $beam->direction->y * -1, 'y' => $beam->direction->x * -1];
                        $this->logger->debug("       Beam {$beam->x},{$beam->y} => {$beam->direction->x},{$beam->direction->y}");
                        break;

                    case '\\':
                        $beam->direction = (object) ['x' => $beam->direction->y, 'y' => $beam->direction->x];
                        $this->logger->debug("       Beam {$beam->x},{$beam->y} => {$beam->direction->x},{$beam->direction->y}");
                        break;

                    case '|':
                        if ($beam->direction->x !== 0) {
                            $beam->direction = (object) ['x' => $beam->direction->y, 'y' => $beam->direction->x];
                            $beams->push((object) [
                                'x' => $beam->x,
                                'y' => $beam->y,
                                'direction' => (object) ['x' => 0, 'y' => $beam->direction->y * -1],
                            ]);
                        
                            $this->logger->debug("   new Beam {$beams->last()->x},{$beams->last()->y} => {$beams->last()->direction->x},{$beams->last()->direction->y}");
                        }
                    
                        $this->logger->debug("       Beam {$beam->x},{$beam->y} => {$beam->direction->x},{$beam->direction->y}");
                        break;

                    case '-':
                        if ($beam->direction->y !== 0) {
                            $beam->direction = (object) ['x' => $beam->direction->y, 'y' => $beam->direction->x];
                            $beams->push((object) [
                                'x' => $beam->x,
                                'y' => $beam->y,
                                'direction' => (object) ['x' => $beam->direction->x * -1, 'y' => 0],
                            ]);
                        
                            $this->logger->debug("   new Beam {$beams->last()->x},{$beams->last()->y} => {$beams->last()->direction->x},{$beams->last()->direction->y}");
                        }

                        $this->logger->debug("       Beam {$beam->x},{$beam->y} => {$beam->direction->x},{$beam->direction->y}");
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
