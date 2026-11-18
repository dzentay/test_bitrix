<?php

declare(strict_types=1);

namespace Vendor\Catalog\Repository;

use DateTimeImmutable;
use PDO;
use PDOException;
use Vendor\Catalog\Domain\Product;
use Vendor\Catalog\Exceptions\ConflictException;

final class ProductRepository
{
    private const SORT_COLUMNS = [
        'name' => 'NAME',
        'price' => 'PRICE',
        'brand' => 'BRAND',
        'created_at' => 'CREATED_AT',
        'in_stock' => 'IN_STOCK',
    ];

    public function __construct(
        private readonly PDO $db
    ) {
    }

    public function paginate(array $params): array
    {
        $where = [];
        $args = [];

        if (!empty($params['q'])) {
            $where[] = "NAME LIKE :q ESCAPE '!'";
            $args['q'] = '%' . $this->escapeLike((string) $params['q']) . '%';
        }

        if (!empty($params['brand'])) {
            $where[] = 'LOWER(BRAND) = LOWER(:brand)';
            $args['brand'] = (string) $params['brand'];
        }

        if ($params['price_from'] !== null) {
            $where[] = 'PRICE >= :price_from';
            $args['price_from'] = (float) $params['price_from'];
        }

        if ($params['price_to'] !== null) {
            $where[] = 'PRICE <= :price_to';
            $args['price_to'] = (float) $params['price_to'];
        }

        if ($params['in_stock'] !== null) {
            $where[] = 'IN_STOCK = :in_stock';
            $args['in_stock'] = (int) $params['in_stock'];
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';

        $countSql = sprintf(
            'SELECT COUNT(*) FROM vendor_catalog_product %s',
            $whereSql
        );

        $stmt = $this->db->prepare($countSql);
        $stmt->execute($args);

        $total = (int) $stmt->fetchColumn();

        $sortColumn = self::SORT_COLUMNS[$params['sort']] ?? 'CREATED_AT';
        $order = strtoupper((string) $params['order']) === 'ASC' ? 'ASC' : 'DESC';

        $limit = (int) $params['per_page'];
        $offset = ((int) $params['page'] - 1) * $limit;

        $sql = sprintf(
            'SELECT ID, NAME, CODE, PRICE, BRAND, IN_STOCK, CREATED_AT, UPDATED_AT
             FROM vendor_catalog_product
             %s
             ORDER BY %s %s, ID DESC
             LIMIT %d OFFSET %d',
            $whereSql,
            $sortColumn,
            $order,
            $limit,
            $offset
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($args);

        $items = array_map(
            static fn(array $row): Product => Product::fromRow($row),
            $stmt->fetchAll()
        );

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    public function create(array $data): Product
    {
        $now = new DateTimeImmutable('now');

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO vendor_catalog_product
                    (NAME, CODE, PRICE, BRAND, IN_STOCK, CREATED_AT, UPDATED_AT)
                 VALUES
                    (:name, :code, :price, :brand, :in_stock, :created_at, NULL)'
            );

            $stmt->execute([
                'name' => $data['name'],
                'code' => $data['code'],
                'price' => $data['price'],
                'brand' => $data['brand'],
                'in_stock' => (int) $data['in_stock'],
                'created_at' => $now->format('Y-m-d H:i:s'),
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new ConflictException('Product with this code already exists', 409, $e);
            }

            throw $e;
        }

        return new Product(
            (int) $this->db->lastInsertId(),
            $data['name'],
            $data['code'],
            Product::formatPrice($data['price']),
            $data['brand'],
            (bool) $data['in_stock'],
            $now
        );
    }

    private function escapeLike(string $value): string
    {
        return str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            $value
        );
    }
}