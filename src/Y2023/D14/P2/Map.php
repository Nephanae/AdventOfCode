<?php
namespace App\Y2023\D14\P2;

use App\Map as MapBase;
use App\Tile;
use Illuminate\Support\Collection;
use Generator;

class Map extends MapBase
{
    public function spinCycle(int $times = 1)
    {
        for ($i = 0; $i < $times; $i++) {
            $this->logger->info(($i + 1) . " / {$times}");
            foreach ($this->getDirections() as $key => $direction) {
                $this->tilt($direction);
            }
        }
    }

    private function tilt(object $direction)
    {
        $roundedRocks = $this->getTiles()->filter(fn ($tile) => (string) $tile === 'O');
        if ($direction->x > 0 || $direction->y > 0) {
            $roundedRocks = $roundedRocks->reverse();
        }

        foreach ($roundedRocks as $roundedRock) {
            $x = $roundedRock->x;
            $y = $roundedRock->y;

            while ($this->isValid($x + $direction->x, $y + $direction->y) && (string) $this->get($x + $direction->x, $y + $direction->y) === '.') {
                $x += $direction->x;
                $y += $direction->y;
            }

            $this->map[$roundedRock->y][$roundedRock->x] = new Tile('.', $roundedRock->x, $roundedRock->y);
            $this->map[$y][$x] = new Tile('O', $x, $y);
        }
    }

    private function getDirections(): Generator
    {
        yield 'NORTH' => (object) ['x' => 0, 'y' => -1];
        yield 'WEST' => (object) ['x' => -1, 'y' => 0];
        yield 'SOUTH' => (object) ['x' => 0, 'y' => 1];
        yield 'EAST' => (object) ['x' => 1, 'y' => 0];
    }

    private function isValid(int $x, int $y): bool
    {
        return !($x < 0 || $x > $this->size->x - 1 || $y < 0 || $y > $this->size->y - 1);
    }
}
