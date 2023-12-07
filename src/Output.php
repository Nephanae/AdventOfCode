<?php
namespace App;

final class Output
{
    public function red(string $string): string
    {
        return "\033[31m{$string}\033[39m";
    }

    public function green(string $string): string
    {
        return "\033[32m{$string}\033[39m";
    }

    public function yellow(string $string): string
    {
        return "\033[33m{$string}\033[39m";
    }

    public function blue(string $string): string
    {
        return "\033[34m{$string}\033[39m";
    }

    public function gray(string $string): string
    {
        return "\033[90m{$string}\033[39m";
    }

    public function bgGray(string $string): string
    {
        return "\033[100m{$string}\033[49m";
    }

    public function whiteBold(string $string): string
    {
        return "\033[01m\033[97m{$string}\033[39m\033[22m";
    }
}

