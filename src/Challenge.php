<?php
namespace App;

use Illuminate\Support\LazyCollection;

abstract class Challenge
{
    protected LazyCollection $input;

    public function __construct(LazyCollection $input)
    {
        $this->input = $input;
    }

    abstract public function resolve(): string;
}
