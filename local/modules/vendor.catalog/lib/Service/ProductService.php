<?php

declare(strict_types=1);

namespace Vendor\Catalog\Service;

use Vendor\Catalog\Cache\CacheServiceInterface;
use Vendor\Catalog\Domain\Product;
use Vendor\Catalog\Repository\ProductRepository;
use Vendor\Catalog\Validation\ProductValidator;

final class ProductService
{
    private const CACHE_PREFIX = 'vendor.catalog:products:list:';

    public function __construct(
        private readonly ProductRepository $repository,
        private readonly ProductValidator $validator,
        private readonly CacheServiceInterface $cache,
        private readonly int $cacheTtl = 60
    ) {
    }

    public function list(array $query): array
    {
        $params = $this->validator->validateList($query);

        $key = self::CACHE_PREFIX . sha1(json_encode($params, JSON_THROW_ON_ERROR));

        return $this->cache->remember($key, $this->cacheTtl, function () use ($params): array {
            $result = $this->repository->paginate($params);

            $total = $result['total'];
            $perPage = $params['per_page'];

            return [
                'data' => array_map(
                    static fn(Product $product): array => $product->toArray(),
                    $result['items']
                ),
                'meta' => [
                    'page' => $params['page'],
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => max(1, (int) ceil($total / max(1, $perPage))),
                ],
            ];
        });
    }

    public function create(array $payload): Product
    {
        $data = $this->validator->validateCreate($payload);

        $product = $this->repository->create($data);

        $this->cache->deleteByPattern(self::CACHE_PREFIX . '*');

        return $product;
    }
}