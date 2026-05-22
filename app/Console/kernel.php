#!/usr/bin/env php
<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Console\Commands\CommandRegistry;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();

$registry = new CommandRegistry();
$registry->run($argv ?? []);
