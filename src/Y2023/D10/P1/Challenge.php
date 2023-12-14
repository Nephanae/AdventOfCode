<?php
namespace App\Y2023\D10\P1;

use App\ChallengeAbstract;
use App\Y2023\D10\P1\Pipe;
use Illuminate\Support\Collection;

class Challenge extends ChallengeAbstract
{
    public function resolve(): string
    {
        $pipes = new Collection();
        $map = $this->input->map(fn($row) => str_split($row))->toArray();
        foreach ($map as $y => $row) {
            foreach ($row as $x => $pipe) {
                $pipe = new Pipe($pipe, $x, $y);
                $map[$y][$x] = $pipe;

                if ($pipe->pipe === 'S') {
                    $pipe->step = 0;
                    $pipes->push($pipe);
                }
            }
        }

        $this->logger->debug($pipes->first());

        $steps = 0;
        while ($pipes->isNotEmpty()) {
            $pipe = $pipes->shift();
            $this->logger->debug($pipe);
            $steps = max($steps, $pipe->step);
            $pipes = $pipes->concat($pipe->findConnectedPipes($map));
        }
        
        return $steps;
    }
}
