<?php

namespace App\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Api\Services\ProductService;
use App\Api\Resources\ProductResource;
use App\Api\Resources\ProductSummaryResource;
use App\Api\Responses\Traits\ApiResponds;

class ProductController
{
    use ApiResponds;

    public function __construct(private ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 20);
            $productsPaginator = $this->productService->getPaginated($perPage);

            $warnings = $this->getWarningsForProducts($productsPaginator->getCollection());

            $responseData = ProductSummaryResource::collection($productsPaginator);

            $meta = [
                'current_page' => $productsPaginator->currentPage(),
                'per_page' => $productsPaginator->perPage(),
                'last_page' => $productsPaginator->lastPage(),
                'total' => $productsPaginator->total(),
            ];

            return count($warnings) > 0
                ? $this->warning($responseData, 'Products loaded with warnings', $warnings, $meta)
                : $this->success($responseData, 'Products loaded successfully', $meta);

        } catch (\Throwable $e) {
            \Log::error('Fatal error in controller:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Error loading products: ' . $e->getMessage(), 500);
        }
    }

    public function show(string $code): JsonResponse
    {
        try {
            $product = $this->productService->getByCode($code);

            if (!$product) {
                return $this->notFound("Product with code $code not found.");
            }

            return $this->success(new ProductResource($product), "Product loaded successfully");
        } catch (\Throwable $e) {
            \Log::error("Error loading product $code: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error("Internal error", 500);
        }
    }

    private function getWarningsForProducts($products): array
    {
        $warnings = [];

        $withoutPrice = $products->filter(fn($p) => empty($p->prices));
        if ($withoutPrice->count() > 0) {
            $warnings[] = $withoutPrice->count() . ' products without prices';
        }

        return $warnings;
    }
}