<?php
namespace App\Y2023\D14\P1;

use App\ChallengeAbstract;
use App\Y2023\D14\P1\Map;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $map = new Map($this->input);

        foreach ($map->getRows() as $row) {
            $this->logger->debug($row->map(fn ($tile) => (string) $tile)->implode(''));
        }

        $this->logger->debug('');

        $map->tilt();

        $weight = 0;
        foreach ($map->getRows() as $y => $row) {
            $roundedRocksCount = $row->filter(fn ($tile) => (string) $tile === 'O')->count();
            $rowWeight = $map->size->y - $y;
            $roundedRocksWeight = $rowWeight * $roundedRocksCount;
            $weight += $roundedRocksWeight;
            $this->logger->debug($row->map(fn ($tile) => (string) $tile)->implode('') . " {$rowWeight} x {$roundedRocksCount} = {$roundedRocksWeight} => {$weight}");
        }

        return $weight;
    }
}
