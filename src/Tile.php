<?php
namespace App;

class Tile
{
    private string $char;
    private int $x;
    private int $y;

    public function __construct(string $char, int $x, int $y)
    {
        $this->char = $char;
        $this->x = $x;
        $this->y = $y;
    }

    public function __get(string $property)
    {
        return match ($property) {
            'char' => $this->char,
            'x' => $this->x,
            'y' => $this->y,
        };
    }

    public function __toString(): string
    {
        return $this->char;
    }
}
