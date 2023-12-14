<?php
namespace App\Y2023\D10\P1;

use Generator;

final class Pipe
{
    const CONNECTIONS = [
        'T' => ['x' => 0, 'y' => -1],
        'R' => ['x' => 1, 'y' => 0],
        'B' => ['x' => 0, 'y' => 1],
        'L' => ['x' => -1, 'y' => 0],
    ];

    const PIPES = [
        '|' => ['T', 'B'],
        '-' => ['L', 'R'],
        'L' => ['T', 'R'],
        'J' => ['T', 'L'],
        '7' => ['L', 'B'],
        'F' => ['R', 'B'],
        '.' => [],
        'S' => ['T', 'R', 'B', 'L'],
    ];

    public string $pipe;
    public int $x;
    public int $y;
    public ?int $step = null;

    public function __construct(string $pipe, int $x, int $y)
    {
        $this->pipe = $pipe;
        $this->x = $x;
        $this->y = $y;
    }

    public function findConnectedPipes(array $map): Generator
    {
        foreach (self::PIPES[$this->pipe] as $direction) {
            $connection = (object) self::CONNECTIONS[$direction];
            $candidate = $map[$this->y + $connection->y][$this->x + $connection->x];

            // Already visited
            if ($candidate->step !== null) {
                continue;
            }

            foreach (self::PIPES[$candidate->pipe] as $candidateDirection) {
                $candidateConnection = (object) self::CONNECTIONS[$candidateDirection];
                if ($candidate->x + $candidateConnection->x === $this->x && $candidate->y + $candidateConnection->y === $this->y) {
                    $candidate->step = $this->step + 1;

                    yield $candidate;
                }
            }
        }
    }

    public function __toString(): string
    {
        return "{$this->pipe} {$this->x}, {$this->y} => {$this->step}";
    }
}
