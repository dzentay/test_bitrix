<?php

declare(strict_types=1);

namespace Vendor\Catalog\Database;

use PDO;
use Vendor\Catalog\Application\Env;

final class ConnectionFactory
{
    public static function create(bool $useDatabase = true): PDO
    {
        $host = Env::get('DB_HOST', '127.0.0.1') ?? '127.0.0.1';
        $port = Env::int('DB_PORT', 3306);
        $database = Env::get('DB_DATABASE', 'catalog') ?? 'catalog';
        $username = Env::get('DB_USERNAME', 'root') ?? 'root';
        $password = Env::get('DB_PASSWORD', '') ?? '';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;%scharset=utf8mb4',
            $host,
            $port,
            $useDatabase ? 'dbname=' . $database . ';' : ''
        );

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}