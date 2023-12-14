<?php
namespace App\Y2023\D10\P2;

use App\ChallengeAbstract;
use App\Y2023\D10\P2\Map;
use App\Y2023\D10\P2\Pipe;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $map = new Map($this->input);

        foreach ($map->getBorderUnvisitedPipes() as $pipe) {
            $pipes = new Collection([$pipe]);
            while ($pipes->isNotEmpty()) {
                $pipe = $pipes->shift();
                $this->logger->info((string) $pipe . " {$pipe->x},{$pipe->y}");
                $pipe->state = Pipe::STATE_OUT;

                foreach ($map->getUnvisitedNeighbors($pipe) as $pipe) {
                    $pipe->state = Pipe::STATE_OUT;
                    $pipes->push($pipe);
                }
            }
        }

        $i = 0;
        foreach ($map->getUnvisitedPipes() as $pipe) {
            $pipe->state = Pipe::STATE_IN;
            if ((string) $pipe !== '*') {
                $i++;
            }
        }

        $this->debug($map);

        return $i;
    }

    private function debug(Map $map): void
    {
        foreach ($map->getZoomedMap() as $row) {
            $this->logger->debug(implode('', array_map(
                fn($pipe) => match ($pipe->state) {
                    Pipe::STATE_LOOP => $this->output->blue($pipe),
                    Pipe::STATE_IN => $this->output->green($pipe),
                    Pipe::STATE_OUT => $this->output->red($pipe),
                    -1 => $this->output->yellow($pipe),
                    default => $pipe,
                },
                $row
            )));
        }
    }
}
