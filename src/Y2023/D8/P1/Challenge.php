<?php
namespace App\Y2023\D8\P1;

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

        $steps = $nodes
            ->filter(fn($node) => substr($node->source, -1) === 'A')
            ->map(function ($node) use ($originalPath, $nodes, $pathCount) {
                $firstNode = $node;
                $steps = $pathReset = $foundLastZ = 0;
                $path = clone $originalPath;
                while ($node !== null && $foundLastZ < 60) {
                // while (substr($node->source, -1) !== 'Z') {
                    fwrite(STDOUT, "\033[s");
                    echo "{$firstNode->source} : {$steps}-{$pathReset} {$path->count()}/{$pathCount} => {$node->source}";
                    if ($path->isEmpty()) {
                        $pathReset++;
                        $path = clone $originalPath;
                    }

                    $direction = $path->shift();
                    $node = $nodes->get($node->$direction);
                    $steps++;

                    fwrite(STDOUT, "\033[u");
                    fwrite(STDOUT, "\033[2K");

                    if (substr($node->source, -1) === 'Z') {
                        $foundLastZ++;
                        echo "{$firstNode->source} ({$firstNode->L},{$firstNode->R}) => {$node->source} ({$node->L},{$node->R}) : {$steps} {$path->count()}/{$pathCount}" . PHP_EOL;
                        if ($foundLastZ > 2) {
                            break;
                        }
                    }

                    if ($node->source === $firstNode->source) {
                        echo "{$firstNode->source} ({$firstNode->L},{$firstNode->R}) => {$node->source} ({$node->L},{$node->R}) : {$steps} {$path->count()}/{$pathCount}" . PHP_EOL;
                    }
                }

                echo "{$firstNode->source} ({$firstNode->L},{$firstNode->R}) => {$node->source} ({$node->L},{$node->R}) : {$steps} {$path->count()}/{$pathCount}" . PHP_EOL;

                return $steps;
            });

        return 0;
    }
}
