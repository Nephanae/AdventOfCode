<?php
namespace App\Y2023\D12\P2;

use App\ChallengeAbstract;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        return $this->input
            ->map(function ($row) {
                list($states, $groups) = explode(' ', $row);

                // unfold
                $states = implode('?', array_fill(0, 5, $states));
                $groups = implode(',', array_fill(0, 5, $groups));

                $this->logger->debug("{$states} {$groups}");
                
                $statesCount = strlen($states);
                $groups = new Collection(explode(',', $groups));
                $lastGroupIndex = $groups->count() - 1;
                $absolutePos = 0;
                $max = $statesCount - ($groups->sum() + $lastGroupIndex);
                $broken = substr_count($states, '#');
                
                foreach ($groups as $index => $length) {
                    $group = [];
                    $count = 0;
                    for ($relativePos = $max; $relativePos >= 0; $relativePos--) {
                        if (!$this->isStatesPartValid($states, $absolutePos + $relativePos, $length)) {
                            continue;
                        }

                        $consumed = 0;
                        for($i = 0; $i < $length; $i++) {
                            if ($states[$absolutePos + $relativePos + $i] === '#') {
                                $consumed++;
                            }
                        }

                        $count++;
                        $group[$relativePos] = [$consumed => 1];
                    }

                    $groups[$index] = $group;
                    $absolutePos += $length + 1;
                }

                $next = $groups->pop();
                while ($groups->isNotEmpty()) {
                    $group = $groups->pop();
                    $newGroup = [];

                    foreach ($group as $pos => $permutations) {
                        foreach (array_filter($next, fn ($nextPos) => $nextPos >= $pos, ARRAY_FILTER_USE_KEY) as $nextPermutations) {
                            foreach ($permutations as $consumed => $count) {
                                foreach( $nextPermutations as $nextConsumed => $nextCount) {
                                    $newConsumed = $consumed + $nextConsumed;

                                    if ($newConsumed > $broken) {
                                        continue;
                                    }
                                    
                                    if (!isset($newGroup[$pos])) {
                                        $newGroup[$pos] = [];
                                    }

                                    if (!isset($newGroup[$pos][$newConsumed])) {
                                        $newGroup[$pos][$newConsumed] = 0;
                                    }

                                    $newGroup[$pos][$newConsumed] += $nextCount;
                                }
                            }
                        }
                    }

                    $next = $newGroup;    
                }

                $count = 0;
                foreach ($newGroup as $pos => $permutations) {
                    $count += $permutations[$broken] ?? 0;
                }

                $this->logger->debug("count : {$count}");

                return $count;
            })
            ->sum();
    }

    private function isStatesPartValid(string $states, int $pos, int $length): bool
    {
        // Previous can't be #
        if ($pos > 0 && $states[$pos - 1] === '#') {
            return false;
        }


        for ($i = $pos; $i < $pos + $length; $i++) {
            if ($states[$i] === '.') {
                return false;
            }
        }

        // Next can't be #
        $next = $pos + $length;
        if ($next < strlen($states) && $states[$next] === '#') {
            return false;
        }

        $this->logger->info(($pos > 0 ? str_repeat(' ', $pos - 1) . '.' : '') . str_repeat('#', $length) . ($length < strlen($states) ? '.' : ''));

        return true;
    }
}
