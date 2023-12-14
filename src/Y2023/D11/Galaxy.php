<?php
namespace App\Y2023\D11;

final class Galaxy
{
    static int $nextId = 0;

    public int $id;
    public int $x;
    public int $y;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;

        $this->id = self::$nextId;
        self::$nextId++;
    }
}
