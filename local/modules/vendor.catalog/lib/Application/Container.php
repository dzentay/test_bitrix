<?php

declare(strict_types=1);

namespace Vendor\Catalog\Application;

use PDO;
use Vendor\Catalog\Cache\CacheServiceInterface;
use Vendor\Catalog\Cache\RedisCacheService;
use Vendor\Catalog\Database\ConnectionFactory;
use Vendor\Catalog\Http\ApiController;
use Vendor\Catalog\Repository\ProductRepository;
use Vendor\Catalog\Service\ProductService;
use Vendor\Catalog\Validation\ProductValidator;

final class Container
{
    private ?PDO $pdo = null;
    private ?CacheServiceInterface $cache = null;
    private ?ProductRepository $repository = null;
    private ?ProductValidator $validator = null;
    private ?ProductService $service = null;
    private ?ApiController $controller = null;

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = ConnectionFactory::create(true);
        }

        return $this->pdo;
    }

    public function cache(): CacheServiceInterface
    {
        if ($this->cache === null) {
            $this->cache = new RedisCacheService(
                Env::get('REDIS_HOST', '127.0.0.1') ?? '127.0.0.1',
                Env::int('REDIS_PORT', 6379)
            );
        }

        return $this->cache;
    }

    public function repository(): ProductRepository
    {
        if ($this->repository === null) {
            $this->repository = new ProductRepository($this->pdo());
        }

        return $this->repository;
    }

    public function validator(): ProductValidator
    {
        if ($this->validator === null) {
            $this->validator = new ProductValidator();
        }

        return $this->validator;
    }

    public function service(): ProductService
    {
        if ($this->service === null) {
            $this->service = new ProductService(
                $this->repository(),
                $this->validator(),
                $this->cache(),
                Env::int('CACHE_TTL', 60)
            );
        }

        return $this->service;
    }

    public function apiController(): ApiController
    {
        if ($this->controller === null) {
            $this->controller = new ApiController($this->service());
        }

        return $this->controller;
    }
}