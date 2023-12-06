<?php
require __DIR__ . '/vendor/autoload.php';

use App\App;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = new App($dotenv);
$app->run($argv);

