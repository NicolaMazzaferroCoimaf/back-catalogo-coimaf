<?php

namespace App\Api\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Api\Services\ProductService;
use App\Api\Resources\ProductResource;
use App\Api\Responses\Traits\ApiResponds;

class ProductController
{
    use ApiResponds;
    
    public function __construct(private ProductService $productService) {}

    public function index(Request $request)
    {
        try {
            // Recupera query parametri
            $perPage = $request->get('per_page', 20); // default 20
            $productsPaginator = $this->productService->getPaginated($perPage);

            // Warnings (prodotti senza prezzi)
            $warnings = [];
            $prodottiSenzaPrezzo = $productsPaginator->getCollection()->filter(fn($p) => empty($p->prezzi));
            if ($prodottiSenzaPrezzo->count() > 0) {
                $warnings[] = $prodottiSenzaPrezzo->count() . ' prodotti senza prezzo';
            }

            // Resource paginata
            $responseData = ProductResource::collection($productsPaginator);

            // Metadata per frontend
            $extra = [
                'current_page' => $productsPaginator->currentPage(),
                'per_page' => $productsPaginator->perPage(),
                'last_page' => $productsPaginator->lastPage(),
                'total' => $productsPaginator->total(),
            ];

            return count($warnings) > 0
                ? $this->warning($responseData, 'Prodotti caricati con avvisi', $warnings, $extra)
                : $this->success($responseData, 'Prodotti caricati con successo', $extra);

        } catch (\Throwable $e) {
            \Log::error('Errore grave nel controller:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return $this->error('Errore nel recupero prodotti: ' . $e->getMessage(), 500);
        }    
    }
}
