<?php
namespace App\Y2023\D14\P1;

use App\Map as MapBase;
use App\Tile;
use Illuminate\Support\Collection;

class Map extends MapBase
{
    public function tilt()
    {
        $roundedRocks = $this->getTiles()->filter(fn ($tile) => (string) $tile === 'O');
        foreach ($roundedRocks as $roundedRock) {
            $y = $roundedRock->y;

            while ($y > 0 && (string) $this->get($roundedRock->x, $y - 1) === '.') {
                $y--;
            }

            $this->map[$roundedRock->y][$roundedRock->x] = new Tile('.', $roundedRock->x, $roundedRock->y);
            $this->map[$y][$roundedRock->x] = new Tile('O', $roundedRock->x, $y);
        }
    }
}
