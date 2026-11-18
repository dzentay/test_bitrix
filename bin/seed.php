<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Vendor\Catalog\Application\Bootstrap;
use Vendor\Catalog\Database\ConnectionFactory;

Bootstrap::init();

$pdo = ConnectionFactory::create(true);

$count = (int) $pdo->query('SELECT COUNT(*) FROM vendor_catalog_product')->fetchColumn();

if ($count > 0) {
    echo "Database already seeded\n";
    exit(0);
}

$products = [
    ['Дрель ударная Bosch GSB 13RE', 'drel-bosch-gsb-13re', '8990.00', 'Bosch', 1, 18],
    ['Перфоратор Makita HR2470', 'perforator-makita-hr2470', '12990.00', 'Makita', 1, 17],
    ['Шуруповерт DeWalt DCD771D2', 'shurupovert-dewalt-dcd771d2', '15490.00', 'DeWalt', 0, 16],
    ['Стиральная машина Samsung WW60J30G0LW', 'stiralnaya-mashina-samsung-ww60j30g0lw', '32990.00', 'Samsung', 1, 15],
    ['Холодильник LG GA-B419SEQ', 'holodilnik-lg-ga-b419seq', '45990.00', 'LG', 1, 14],
    ['Смартфон Xiaomi Redmi Note 13', 'smartphone-xiaomi-redmi-note-13', '19990.00', 'Xiaomi', 1, 13],
    ['Ноутбук Samsung Galaxy Book4', 'noutbuk-samsung-galaxy-book4', '74990.00', 'Samsung', 0, 12],
    ['Телевизор LG 50UR78006LK', 'televizor-lg-50ur78006lk', '39990.00', 'LG', 1, 11],
    ['Пылесос Bosch BGL25P110', 'vacuum-bosch-bgl25p110', '10990.00', 'Bosch', 1, 10],
    ['Газонокосилка Makita ELM3320', 'gaznokosilka-makita-elm3320', '17990.00', 'Makita', 0, 9],
    ['Дрель-шуруповерт Bosch GSR 120-LI', 'drel-shurupovert-bosch-gsr-120-li', '9990.00', 'Bosch', 1, 8],
    ['Перфоратор DeWalt D25133K', 'perforator-dewalt-d25133k', '18990.00', 'DeWalt', 1, 7],
    ['Стиральная машина Haier HW60-BP12929', 'stiralnaya-mashina-haier-hw60-bp12929', '37990.00', 'Haier', 1, 6],
    ['Холодильник Samsung RB33J3000SA', 'holodilnik-samsung-rb33j3000sa', '52990.00', 'Samsung', 0, 5],
    ['Смартфон Samsung Galaxy A15', 'smartphone-samsung-galaxy-a15', '14990.00', 'Samsung', 1, 4],
    ['Телевизор Xiaomi TV A2 32', 'televizor-xiaomi-tv-a2-32', '18990.00', 'Xiaomi', 1, 3],
    ['Пылесос Xiaomi Vacuum Cleaner G11', 'vacuum-xiaomi-vacuum-g11', '21990.00', 'Xiaomi', 0, 2],
    ['Ноутбук LG Gram 14', 'noutbuk-lg-gram-14', '99990.00', 'LG', 1, 1],
];

$stmt = $pdo->prepare(
    'INSERT INTO vendor_catalog_product
        (NAME, CODE, PRICE, BRAND, IN_STOCK, CREATED_AT, UPDATED_AT)
     VALUES
        (:name, :code, :price, :brand, :in_stock, :created_at, NULL)'
);

foreach ($products as $product) {
    [$name, $code, $price, $brand, $inStock, $daysAgo] = $product;

    $createdAt = (new DateTimeImmutable(sprintf('-%d days', $daysAgo)))
        ->setTime(12, 0, 0)
        ->format('Y-m-d H:i:s');

    $stmt->execute([
        'name' => $name,
        'code' => $code,
        'price' => $price,
        'brand' => $brand,
        'in_stock' => $inStock,
        'created_at' => $createdAt,
    ]);
}

echo "Seeded " . count($products) . " products\n";