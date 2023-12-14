<?php
namespace App\Y2023\D10\P2;

final class Pipe
{
    const STATE_UNVISITED = 0;
    const STATE_LOOP = 1;
    const STATE_IN = 2;
    const STATE_OUT = 3;

    private string $pipe;
    public int $x;
    public int $y;
    public int $state = 0;

    public function __construct(string $pipe, int $x, int $y)
    {
        $this->pipe = $pipe;
        $this->x = $x;
        $this->y = $y;
    }

    public function getConnections(): array
    {
        return match ($this->pipe) {
            '|' => [(object) ['x' => 0, 'y' => -1], (object) ['x' => 0, 'y' => 1]],
            '-' => [(object) ['x' => -1, 'y' => 0], (object) ['x' => 1, 'y' => 0]],
            'L' => [(object) ['x' => 0, 'y' => -1], (object) ['x' => 1, 'y' => 0]],
            'J' => [(object) ['x' => 0, 'y' => -1], (object) ['x' => -1, 'y' => 0]],
            '7' => [(object) ['x' => -1, 'y' => 0], (object) ['x' => 0, 'y' => 1]],
            'F' => [(object) ['x' => 1, 'y' => 0], (object) ['x' => 0, 'y' => 1]],
            '.' => [],
            'S' => [
                (object) ['x' => 0, 'y' => -1],
                (object) ['x' => 1, 'y' => 0],
                (object) ['x' => 0, 'y' => 1],
                (object) ['x' => -1, 'y' => 0],
            ],
        };
    }

    public function __toString(): string
    {
        return $this->pipe;
        return match ($this->state) {
            self::STATE_LOOP => $this->output->blue($this->pipe),
            self::STATE_IN => $this->output->green($this->pipe),
            self::STATE_OUT => $this->output->red($this->pipe),
            default => $this->pipe,
        };
    }
}
