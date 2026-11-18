<?php

declare(strict_types=1);

$loader = dirname(__DIR__, 4) . '/vendor/autoload.php';

if (is_file($loader)) {
    require_once $loader;
}

if (class_exists(\Vendor\Catalog\Application\Bootstrap::class)) {
    \Vendor\Catalog\Application\Bootstrap::init();
}