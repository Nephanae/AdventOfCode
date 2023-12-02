<?php 
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\LazyCollection;

return new LazyCollection(function () {
    $fp = fopen('php://stdin', 'r');
    while ($line = fgets($fp)) {
        yield $line;
    }

    fclose($fp);
});
