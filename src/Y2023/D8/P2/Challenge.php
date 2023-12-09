<?php
namespace App\Y2023\D8\P2;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $input = $this->input->collect();

        $originalPath = new Collection(str_split($input->shift()));
        $pathCount = $originalPath->count();

        $nodes = $input
            ->filter()
            ->mapWithKeys(function ($row) {
                list($source, $target) = explode(' = ', $row);
                list($left, $right) = explode(', ', $target);

                return [$source => (object) ['source' => $source, 'L' => substr($left, 1), 'R' => substr($right, 0, -1)]];
            })
            ->collect();

        $turns = $nodes
            ->filter(fn($node) => substr($node->source, -1) === 'A')
            ->map(function ($node) use ($originalPath, $nodes, $pathCount) {
                $firstNode = $node; // For logging purpose
                $steps = $pathReset = 0; // $steps: For logging purpose
                $path = new Collection();
                while (substr($node->source, -1) !== 'Z') {
                    $this->logger->info("{$firstNode->source} : {$steps}-{$pathReset} {$path->count()}/{$pathCount} => {$node->source}");
                    if ($path->isEmpty()) {
                        $pathReset++;
                        $path = clone $originalPath;
                    }

                    $direction = $path->shift();
                    $node = $nodes->get($node->$direction);
                    $steps++;
                }

                $this->logger->notice("{$firstNode->source} ({$firstNode->L},{$firstNode->R}) => {$node->source} ({$node->L},{$node->R}) : {$steps} {$path->count()}/{$pathCount} ({$pathReset})");

                return $pathReset;
            });

        $init = $turns->shift();
        $lcm = $turns->reduce(fn($lcm, $turn) => $this->lcm($lcm, $turn), $init);

        return $lcm * $pathCount;
    }

    private function lcm(int $a, int $b): int
    {
        list($a, $b) = [min($a, $b), max($a, $b)];
        $i = $b;
        while ($i % $a !== 0) {
            $i += $b;
        }

        return $i;
    }
}
