<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Vendor\Catalog\Application\Bootstrap;
use Vendor\Catalog\Application\Env;
use Vendor\Catalog\Database\ConnectionFactory;

Bootstrap::init();

$database = Env::get('DB_DATABASE', 'catalog') ?? 'catalog';

try {
    $server = ConnectionFactory::create(false);
    $server->exec(
        sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            str_replace('`', '', $database)
        )
    );
} catch (Throwable $e) {
    fwrite(STDERR, 'Warning: cannot create database: ' . $e->getMessage() . "\n");
}

$pdo = ConnectionFactory::create(true);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS vendor_catalog_product (
        ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
        NAME VARCHAR(255) NOT NULL,
        CODE VARCHAR(255) NOT NULL,
        PRICE DECIMAL(12,2) NOT NULL DEFAULT 0,
        BRAND VARCHAR(255) NULL,
        IN_STOCK TINYINT(1) NOT NULL DEFAULT 0,
        CREATED_AT DATETIME NOT NULL,
        UPDATED_AT DATETIME NULL,
        PRIMARY KEY (ID),
        UNIQUE KEY uq_vendor_catalog_product_code (CODE),
        KEY idx_vendor_catalog_product_brand (BRAND),
        KEY idx_vendor_catalog_product_price (PRICE),
        KEY idx_vendor_catalog_product_created (CREATED_AT),
        KEY idx_vendor_catalog_product_stock (IN_STOCK)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

echo "Migration complete\n";