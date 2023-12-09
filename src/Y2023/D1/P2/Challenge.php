<?php
namespace App\Y2023\D1\P2;

use App\ChallengeAbstract;

class Challenge extends ChallengeAbstract
{
    const DIGITS = [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        'one' => 1,
        'two' => 2,
        'three' => 3,
        'four' => 4,
        'five' => 5,
        'six' => 6,
        'seven' => 7,
        'eight' => 8,
        'nine' => 9,
    ];

    public function resolve(): string
    {
        return $this->input
            ->filter()
            ->map(function ($row) {
                $digits = '';
                while (($digit = $this->findFirstDigit($row)) !== false) {
                    $digits .= $digit;
                    $row = substr($row, 1);
                }

                return substr($digits, 0, 1) . substr($digits, -1);
            })
            ->sum();
    }

    private function findFirstDigit($string)
    {
        $result = false;
        foreach (self::DIGITS as $digitString => $digit) {
            $pos = strpos($string, $digitString);
            if ($pos !== false && ($result === false || $pos < $result->pos)) {
                $result = (object) ['pos' => $pos, 'digit' => $digit];
            }
        }

        return $result->digit ?? false;
    }
}
