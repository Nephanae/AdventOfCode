<?php
namespace App\Y2023\D15\P2;

use App\ChallengeAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $input = $this->input->first();
        $input = new Collection(explode(',', $input));

        $boxes = new Collection();

        foreach ($input as $instruction) {
            $dashPos = strpos($instruction, '-');
            if ($dashPos !== false) {
                $label = substr($instruction, 0, $dashPos);
                $box = $this->hash($label);

                if ($boxes->has($box) && $boxes[$box]->has($label)) {
                    $boxes[$box]->forget($label);
                }
                
                continue;
            }

            list($label, $focalLength) = explode('=', $instruction);
            $box = $this->hash($label);

            if (!$boxes->has($box)) {
                $boxes->put($box, new Collection());
            }

            if ($boxes[$box]->has($label)) {
                $boxes[$box][$label] = $focalLength;

                continue;
            }

            $boxes[$box]->put($label, $focalLength);
        }

        $this->logger->debug(print_r($boxes->toArray(), true));

        return $this->getBoxesScores($boxes)->sum();
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

    private function getBoxesScores(Collection $boxes): LazyCollection
    {
        return new LazyCollection(function () use ($boxes) {
            foreach ($boxes as $index => $box) {
                foreach ($box->values() as $lens => $focalLength) {
                    yield ($index + 1) * ($lens + 1) * $focalLength;
                }
            }
        });
    }
}
