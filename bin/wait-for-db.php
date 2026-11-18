<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Vendor\Catalog\Application\Env;
use Vendor\Catalog\Database\ConnectionFactory;

Env::load();

$limit = time() + 60;

while (time() < $limit) {
    try {
        $pdo = ConnectionFactory::create(false);
        $pdo->query('SELECT 1');

        echo "Database is ready\n";
        exit(0);
    } catch (Throwable) {
        echo "Waiting for database...\n";
        sleep(2);
    }
}

fwrite(STDERR, "Database is not ready\n");
exit(1);