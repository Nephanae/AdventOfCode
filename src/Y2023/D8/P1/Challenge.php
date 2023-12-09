<?php
namespace App\Y2023\D8\P1;

use App\Challenge as BaseChallenge;
use Illuminate\Support\Collection;

class Challenge extends BaseChallenge
{
    public function resolve(): string
    {
        $input = $this->input->collect();

        $originalPath = new Collection(str_split($input->shift()));

        $nodes = $input
            ->filter()
            ->mapWithKeys(function ($row) {
                list($source, $target) = explode(' = ', $row);
                list($left, $right) = explode(', ', $target);

                return [$source => (object) ['source' => $source, 'L' => substr($left, 1), 'R' => substr($right, 0, -1)]];
            })
            ->collect();

        $steps = 0;
        $path = clone $originalPath;
        $node = $nodes->get('AAA');
        while ($node->source != 'ZZZ') {
            if ($path->isEmpty()) {
                $path = clone $originalPath;
            }

            $direction = $path->shift();
            $node = $nodes->get($node->$direction);
            $steps++;
        }

        return $steps;
    }
}
