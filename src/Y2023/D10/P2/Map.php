<?php
namespace App\Y2023\D10\P2;

use App\Y2023\D10\P2\Pipe;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use RangeException;
use stdClass;

final class Map
{
    private Collection $pipes;
    private Pipe $start;
    private array $map;
    private array $zoomedMap;
    private stdClass $mapSize;
    private stdClass $zoomedMapSize;

    public function __construct(LazyCollection $input)
    {
        $this->map = $input->map(fn($row) => str_split($row))->toArray();

        foreach ($this->map as $y => $row) {
            foreach ($row as $x => $pipe) {
                $pipe = new Pipe($pipe, $x, $y);
                $this->map[$y][$x] = $pipe;

                if ((string) $pipe === 'S') {
                    $this->start = $pipe;
                }
            }
        }

        $this->mapSize = (object) ['x' => count($this->map[0]), 'y' => count($this->map)];

        $this->zoom();
        $this->zoomedMapSize = (object) ['x' => count($this->zoomedMap[0]), 'y' => count($this->zoomedMap)];

        $this->setLoop();
    }

    public function get(int $x, int $y): Pipe
    {
        if ($x < 0 || $x > $this->mapSize->x - 1 || $y < 0 || $y > $this->mapSize->y - 1) {
            throw new RangeException("Pipe::get({$x}, {$y} : Out of map");
        }

        return $this->map[$y][$x];
    }

    public function getZoomedPipe(int $x, int $y): Pipe
    {
        if ($x < 0 || $x > $this->zoomedMapSize->x - 1 || $y < 0 || $y > $this->zoomedMapSize->y - 1) {
            throw new RangeException("Pipe::get({$x}, {$y} : Out of map");
        }

        return $this->zoomedMap[$y][$x];
    }

    public function getStart(): Pipe
    {
        return $this->start;
    }

    public function getZoomedMap(): array
    {
        return $this->zoomedMap;
    }

    public function getBorderUnvisitedPipes(): Generator
    {
        $lastX = $this->zoomedMapSize->x - 1;
        $lastY = $this->zoomedMapSize->y - 1;

        foreach ($this->zoomedMap as $y => $row) {
            foreach ([$row[0], $this->zoomedMap[$y][$lastX]] as $pipe) {
                if ($pipe->state === Pipe::STATE_UNVISITED) {
                    yield $pipe;
                }
            }
        }

        foreach ($this->zoomedMap[0] as $x => $pipe) {
            foreach ([$pipe, $this->zoomedMap[$lastY][$x]] as $pipe) {
                if ($pipe->state === Pipe::STATE_UNVISITED) {
                    yield $pipe;
                }
            }
        }
    }

    public function getUnvisitedNeighbors(Pipe $pipe): Generator
    {
        for ($y = -1; $y < 2; $y++) {
            for ($x = -1; $x < 2; $x++) {
                try {
                    $neighbor = $this->getZoomedPipe($pipe->x + $x, $pipe->y + $y);
                    if ($neighbor->state === Pipe::STATE_UNVISITED) {
                        yield $neighbor;
                    }
                } catch (RangeException $e) {
                }
            }
        }
    }

    public function getUnvisitedPipes(): Generator
    {
        foreach ($this->zoomedMap as $row) {
            foreach ($row as $pipe) {
                if ($pipe->state === Pipe::STATE_UNVISITED) {
                    yield $pipe;
                }
            }
        }
    }

    public function toArray(): array
    {
        return $this->map;
    }

    private function setLoop()
    {
        $this->start->state = Pipe::STATE_LOOP;
        $this->zoomedMap[$this->start->y * 2][$this->start->x * 2]->state = Pipe::STATE_LOOP;
        $pipes = new Collection([$this->start]);

        while ($pipes->isNotEmpty()) {
            $pipe = $pipes->shift();
            $pipes = $pipes->concat($this->getConnectedLoopPipes($pipe));
        }
    }

    private function getConnectedLoopPipes(Pipe $pipe): Generator
    {
        foreach ($pipe->getConnections() as $connection) {
            $candidate = $this->get($pipe->x + $connection->x, $pipe->y + $connection->y);

            foreach ($candidate->getConnections() as $candidateConnection) {
                if ($candidate->x + $candidateConnection->x === $pipe->x && $candidate->y + $candidateConnection->y === $pipe->y) {

                    // Zoomed pipes
                    $this->zoomedMap[$candidate->y * 2][$candidate->x * 2]->state = Pipe::STATE_LOOP;
                    $this->zoomedMap[$candidate->y * 2 + $candidateConnection->y][$candidate->x * 2 + $candidateConnection->x]->state = Pipe::STATE_LOOP;

                    if ($candidate->state === Pipe::STATE_UNVISITED) {
                        $candidate->state = Pipe::STATE_LOOP;

                        yield $candidate;
                    }
                }
            }
        }
    }

    private function zoom()
    {
        foreach ($this->map as $y => $row) {
            $this->zoomedMap[$y * 2] = [];
            $this->zoomedMap[$y * 2 + 1] = [];

            foreach ($row as $x => $pipe) {
                $this->zoomedMap[$y * 2][$x * 2] = new Pipe((string) $pipe, $x * 2, $y * 2);
                $this->zoomedMap[$y * 2 + 1][$x * 2] = new Pipe('*', $x * 2, $y * 2 + 1);
                $this->zoomedMap[$y * 2][$x * 2 + 1] = new Pipe('*', $x * 2 + 1, $y * 2);
                $this->zoomedMap[$y * 2 + 1][$x * 2 + 1] = new Pipe('*', $x * 2 + 1, $y * 2 + 1);
            }

            // Delete last X
            array_pop($this->zoomedMap[$y * 2]);
            array_pop($this->zoomedMap[$y * 2 + 1]);
        }

        // Delete last Y
        array_pop($this->zoomedMap);
    }
}
