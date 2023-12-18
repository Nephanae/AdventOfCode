<?php
namespace App\Y2023\D12\P1;

use App\ChallengeAbstract;
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
                    'states' => new Collection(str_split($states)),
                    'groups' => new Collection(explode(',', $groups)),
                ];
            })
            ->map(function ($row) {
                $this->logger->debug("{$row->states->implode('')} {$row->groups->implode(',')}");

                return $this
                    ->getPermutations($row->states)
                    ->filter(fn ($states) => $this->isValid($states, $row->groups))
                    ->count();
            })
            ->sum();
    }

    private function getPermutations(Collection $states): LazyCollection
    {
        return new LazyCollection(function () use ($states) {
            $states = clone $states;

            $unknowns = $states
                ->filter(fn ($state) => $state === '?')
                ->map(fn ($state, $key) => $key)
                ->values()
                ->toArray();

            $length = count($unknowns);
            $max = bindec(str_repeat(1, $length));
            
            for ($i = 0; $i < $max + 1; $i++) {
                $bin = str_split(sprintf("%0{$length}d", decbin($i)));
                foreach ($bin as $index => $value) {
                    $states->put($unknowns[$index], $value ? '#' : '.');
                }

                $this->logger->info($states->implode(''));

                yield $states->implode('');
            }
        });
    }

    private function isValid(string $states, Collection $groups): bool
    {
        $matches = [];
        preg_match_all('/(#+)/', $states, $matches);

        if (count($matches[0]) !== $groups->count()) {
            return false;
        }

        foreach ($groups as $index => $length) {
            if (strlen($matches[0][$index]) != $length) {
                return false;
            }
        }

        $this->logger->debug($states);

        return true;
    }
}
