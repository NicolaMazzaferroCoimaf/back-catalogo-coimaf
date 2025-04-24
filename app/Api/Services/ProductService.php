<?php

namespace App\Api\Services;

use App\Api\Repositories\ProductRepository;
use App\Api\Repositories\ProductUnitRepository;
use App\Api\Repositories\ProductImageRepository;
use App\Api\Repositories\ProductPriceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Api\Dtos\ProductDto;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductPriceRepository $productPriceRepository,
        private ProductUnitRepository $productUnitRepository,
        private ProductImageRepository $productImageRepository
    ) {}

    public function getAll(): array
    {
        $products = $this->productRepository->getAll();
        return $this->loadProductDetails($products);
    }

    public function getPaginated(int $perPage = 100): LengthAwarePaginator
    {
        $paginator = $this->productRepository->getPaginated($perPage);
        $products = $paginator->getCollection();
        $paginator->setCollection(collect($this->loadProductDetails($products)));

        return $paginator;
    }

    public function getByCode(string $code): ?ProductDto
    {
        $product = $this->productRepository->findByCode($code);

        if (!$product) {
            return null;
        }

        $unitsByProduct = $this->productUnitRepository->getUnitsByProduct();
        $flatPrices = $this->productPriceRepository->getLatestPricesForProducts($unitsByProduct);
        $imagesByProduct = $this->productImageRepository->getImagesByProduct([$code]);

        $prices = $flatPrices[$code] ?? [];
        $product->prices = $this->groupPricesByList($prices);
        $product->images = $imagesByProduct[$code] ?? [];

        return $product;
    }

    private function loadProductDetails(iterable $products): iterable
    {
        $codes = collect($products)->pluck('code')->toArray();

        $unitsByProduct = $this->productUnitRepository->getUnitsByProduct();
        $flatPrices = $this->productPriceRepository->getLatestPricesForProducts($unitsByProduct);
        $imagesByProduct = $this->productImageRepository->getImagesByProduct($codes);

        foreach ($products as $product) {
            $code = $product->code;
            $prices = $flatPrices[$code] ?? [];

            $product->prices = $this->groupPricesByList($prices);
            $product->images = $imagesByProduct[$code] ?? [];
        }

        return $products;
    }

    private function groupPricesByList(array $prices): array
    {
        $result = [];

        foreach ($prices as $entry) {
            $requiredFields = [
                'list_code', 'list_name', 'unit',
                'factor', 'default', 'price', 'discount', 'net_price'
            ];            

            foreach ($requiredFields as $field) {
                if (!array_key_exists($field, $entry)) {
                    \Log::warning("Missing field [$field]", ['entry' => $entry]);
                    continue 2;
                }
            }

            $listCode = $entry['list_code'];

            $result[$listCode] ??= [
                'list' => $entry['list_name'],
                'items' => [],
            ];
            
            $result[$listCode]['items'][] = [
                'unit' => $entry['unit'],
                'factor' => $entry['factor'],
                'default' => $entry['default'],
                'price' => $entry['price'],
                'discount' => $entry['discount'],
                'net_price' => $entry['net_price'],
            ];            
            
        }

        return $result;
    }
}