<?php

if (!is_file(__DIR__ . '/../vendor/autoload.php')) {
    throw new \RuntimeException('Install dependencies to run test suite.');
}

$loader = require __DIR__ . '/../vendor/autoload.php';
