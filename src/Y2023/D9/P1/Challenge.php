<?php
namespace App\Y2023\D9\P1;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        return $this->input
            ->map(function ($history) {
                $sequence = $history = new Collection(explode(' ', $history));
                $sequences = new Collection([$history]);

                $sequenceIndex = 0;
                while (!$sequences->last()->every(fn ($value) => $value === 0)) {
                    $this->debugSequence($sequence, $sequenceIndex);
                    $newSequence = new Collection();
                    $count = $sequence->count();
                    $value = $sequence->first();
                    for ($i = 1; $i < $count; $i++) {
                        $newSequence->push($sequence->get($i) - $value);
                        $value = $sequence->get($i);
                    }

                    $sequences->push($newSequence);
                    $sequence = $newSequence;
                    $sequenceIndex++;
                }

                $this->debugSequence($sequence, $sequenceIndex);
                
                $previous = 0;
                for ($i = $sequences->count() - 1; $i >= 0; $i--) {
                    $previous += $sequences->get($i)->last();
                    $sequences->get($i)->push($previous);
                }

                foreach ($sequences as $sequenceIndex => $sequence) {
                    $this->debugSequence($sequence, $sequenceIndex);
                }

                return $sequences->first()->last();
            })
            ->sum();
    }

    private function debugSequence(Collection $sequence, int $key): void
    {
        $this->logger->notice(str_repeat(' ', $key) . $sequence->implode(' '));
    }

    private function debugSequences(Collection $sequences): void
    {
        foreach ($sequences as $key => $sequence) {
            $this->logger->notice(str_repeat(' ', $key) . $sequence->implode(' '));
        }
    }
}
