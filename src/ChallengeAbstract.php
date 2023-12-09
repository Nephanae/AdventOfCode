<?php
namespace App;

use App\Output;
use Illuminate\Support\LazyCollection;

abstract class ChallengeAbstract
{
    protected LazyCollection $input;
    protected Output $output;

    public function __construct(LazyCollection $input)
    {
        $this->input = $input;
        $this->output = new Output();
    }

    abstract public function resolve(): string;
}

