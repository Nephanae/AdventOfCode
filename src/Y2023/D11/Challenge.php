<?php
namespace App\Y2023\D11;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

abstract class Challenge extends ChallengeAbstract
{
    const EXPANSION = 0;

    public function resolve(): string
    {
        $input = $this->input->map(fn($row) => new Collection(str_split($row)))->collect();
        $mapSize = (object) ['x' => $input->first()->count(), 'y' => $input->count()];

        $galaxies = new Collection();
        $emptyCols = new Collection(array_fill(0, $mapSize->x, 0));
        $emptyRows = new Collection(array_fill(0, $mapSize->y, 0));

        foreach ($input as $y => $row) {
            foreach ($row as $x => $col) {
                if ($col !== '#') {
                    continue;
                }

                $emptyRows->forget($y);
                $emptyCols->forget($x);

                $galaxies->push(new Galaxy($x, $y));
            }
        }

        unset($input);

        // Expand universe
        $emptyCols = $emptyCols->reverse();
        $emptyRows = $emptyRows->reverse();

        foreach ($emptyCols as $x => $emptyCol) {
            $galaxies
                ->filter(fn ($galaxy) => $galaxy->x > $x)
                ->each(fn ($galaxy) => $galaxy->x += static::EXPANSION);
        }

        foreach ($emptyRows as $y => $emptyRow) {
            $galaxies
                ->filter(fn ($galaxy) => $galaxy->y > $y)
                ->each(fn ($galaxy) => $galaxy->y += static::EXPANSION);
        }

        unset($emptyCols, $emptyRows);

        // Calcul distances
        $pairs = new Collection();
        $distances = new Collection();

        foreach ($galaxies as $source) {
            foreach ($galaxies as $target) {
                if ($source === $target) {
                    continue;
                }

                if ($pairs->get($source->id, new Collection())->contains($target->id)) {
                    continue;
                }

                if ($pairs->get($target->id, new Collection())->contains($source->id)) {
                    continue;
                }

                if (!$pairs->has($source->id)) {
                    $pairs->put($source->id, new Collection());
                }

                $pairs->get($source->id)->push($target->id);

                $distances->push(max($source->x, $target->x) - min($source->x, $target->x) + max($source->y, $target->y) - min($source->y, $target->y));
            }
        }

        return $distances->sum();
    }
}
