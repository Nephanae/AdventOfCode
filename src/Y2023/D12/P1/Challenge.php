<?php
namespace App\Y2023\D12\P1;

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

                return (object) [
                    'states' => $states,
                    'groups' => new Collection(explode(',', $groups)),
                ];
            })
            ->map(function ($row) {
                $this->logger->debug("{$row->states} {$row->groups->implode(',')}");
                $count = $this->getPermutations($row->states, $row->groups)->count();

                $this->logger->debug($count);

                return $count;
            })
            ->sum();
    }

    private function getPermutations(string $states, Collection $groups): LazyCollection
    {
        return new LazyCollection(function () use ($states, $groups) {
            $statesCount = strlen($states);
            $max = $statesCount - ($groups->sum() + $groups->count() - 1);

            foreach ($this->getGroupPermutations($groups, 0, $states, $statesCount) as $permutation) {
                $this->logger->info($permutation);
                
                yield $permutation;
            }
        });
    }

    private function getGroupPermutations(Collection $groups, int $index, string $states, int $statesCount, string $previous = ''): Generator
    {
        $groupLength = $groups->get($index);
        $nextGroups = $groups->skip($index);
        $max = $statesCount - strlen($previous) - $nextGroups->sum() - $nextGroups->count() + 2;
        $isLast = $index === $groups->count() - 1;

        for ($pos = 0; $pos < $max; $pos++) {
            $permutation = $previous . str_repeat('.', $pos) . str_repeat('#', $groupLength);
            $permutation .= !$isLast ? '.' : str_repeat('.', $statesCount - strlen($permutation));

            if (!$this->isValid($permutation, $states)) {
                continue;
            }

            $isLast ? yield $permutation : yield from $this->getGroupPermutations($groups, $index + 1, $states, $statesCount, $permutation);
        }
    }

    private function isValid(string $permutation, string $states): bool
    {
        $iCount = strlen($permutation);
        for ($i = 0; $i < $iCount; $i++) {
            if ($states[$i] !== '?' && $permutation[$i] !== $states[$i]) {
                return false;
            }
        }

        return true;
    }
}
