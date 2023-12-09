<?php
namespace App\Y2023\D8\P2;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $input = $this->input->collect();

        $path = new Collection(str_split($input->shift()));

        $nodes = $input
            ->filter()
            ->mapWithKeys(function ($row) {
                list($source, $target) = explode(' = ', $row);
                list($left, $right) = explode(', ', $target);

                return [$source => (object) ['source' => $source, 'L' => substr($left, 1), 'R' => substr($right, 0, -1)]];
            })
            ->collect();

        $steps = 0;
        $currentPath = clone $path;
        $currentNodes = $nodes->filter(fn($node) => substr($node->source, -1) === 'A');
        // echo "Starting nodes : {$this->currentNodes}" . PHP_EOL;
        while ($currentNodes->contains(fn($node) => substr($node->source, -1) !== 'Z')) {
            if ($currentPath->isEmpty()) {
                // echo 'Reset path' . PHP_EOL;
                $currentPath = clone $path;
            }

            $direction = $currentPath->shift();
            $currentNodes = $currentNodes->map(fn($node) => $nodes->get($node->$direction));
            // echo "{$direction} => {$this->nodes($currentNodes)}" . PHP_EOL;
            $steps++;
            // usleep(100000);
        }

        return $steps;
    }

    private function node(\stdClass $node): string
    {
        return "{$node->source} ({$node->L}, {$node->R})";
    }

    private function nodes(Collection $nodes): string
    {
        return str_replace('Z', $this->output->green('Z'), $nodes->map(fn($node) => $this->node($node))->implode(', '));
    }
}
