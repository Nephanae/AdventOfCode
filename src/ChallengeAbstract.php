<?php
namespace App;

use App\Output;
use Illuminate\Support\LazyCollection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

abstract class ChallengeAbstract implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected LazyCollection $input;
    protected Output $output;

    public function __construct(LazyCollection $input)
    {
        $this->input = $input;
        $this->output = new Output();
        $this->logger = new NullLogger();
    }

    abstract public function resolve(): string;
}

