<?php
namespace App\Y2023\D15\P1;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $input = $this->input->first();
        $input = new Collection(explode(',', $input));

        return $input
            ->map(function ($string) {
                $hash = $this->hash($string);

                $this->logger->debug("{$string} => {$hash}");

                return $hash;
            })
            ->sum();
    }

    private function hash(string $string): int
    {
        $string = str_split($string);
        $value = 0;

        while (!empty($string)) {
            $char = array_shift($string);
            $value += ord($char);
            $value *= 17;
            $value = $value % 256;
        }

        return $value;
    }
}
