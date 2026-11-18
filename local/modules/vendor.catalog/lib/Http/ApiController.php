<?php

declare(strict_types=1);

namespace Vendor\Catalog\Http;

use Vendor\Catalog\Service\ProductService;

final class ApiController
{
    public function __construct(
        private readonly ProductService $service
    ) {
    }

    public function list(Request $request): Response
    {
        $result = $this->service->list($request->query);

        return Response::json($result, 200);
    }

    public function create(Request $request): Response
    {
        if ($request->jsonError) {
            return Response::json([
                'message' => 'Invalid JSON body',
            ], 400);
        }

        $product = $this->service->create($request->body);

        return Response::json([
            'data' => $product->toArray(),
        ], 201);
    }
}