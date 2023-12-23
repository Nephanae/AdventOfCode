<?php
namespace App;

use App\Tile;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use RangeException;
use stdClass;

class Map implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected array $map;
    protected stdClass $size;

    public function __construct(Enumerable $input, string $tileClass = Tile::class)
    {
        $this->logger = new NullLogger();

        $this->map = $input->map(fn($row) => str_split($row))->toArray();

        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $char) {
                $tile = new $tileClass($char, $x, $y);
                $this->map[$y][$x] = $tile;
            }
        }

        $this->size = (object) ['x' => count($this->map[0]), 'y' => count($this->map)];
    }

    public function __get(string $property)
    {
        return match ($property) {
            'size' => $this->getSize(),
        };
    }

    public function get(int $x, int $y): Tile
    {
        if (!$this->has($x, $y)) {
            throw new RangeException("Pipe::get({$x}, {$y} : Out of map");
        }

        return $this->map[$y][$x];
    }

    public function has(int $x, int $y): bool
    {
        return $x >= 0 && $x < $this->size->x && $y >= 0 && $y < $this->size->y;
    }

    public function getCol(int $x): LazyCollection
    {
        return new LazyCollection(function () use ($x) {
            foreach ($this->map as $row) {
                yield $row[$x];
            }
        });
    }

    public function getCols(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->map[0] as $index => $tile) {
                yield $this->getCol($index);
            }
        });
    }

    public function getRow(int $y): LazyCollection
    {
        return new LazyCollection(function () use ($y) {
            foreach ($this->map[$y] as $tile) {
                yield $tile;
            }
        });
    }

    public function getRows(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->map as $index => $row) {
                yield $this->getRow($index);
            }
        });
    }

    public function getTiles(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->map as $row) {
                foreach ($row as $tile) {
                    yield $tile;
                }
            }
        });
    }

    public function getSize(): stdClass
    {
        return $this->size;
    }

    public function toArray(): array
    {
        return $this->map;
    }
}
