<?php

namespace App\Api\Services;

use App\Api\Repositories\ProductRepository;
use App\Api\Repositories\ProductUnitRepository;
use App\Api\Repositories\ProductImageRepository;
use App\Api\Repositories\ProductPriceRepository;

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
        // 1. Recupero tutti i prodotti
        $prodotti = $this->productRepository->getAll();

        // 2. Recupero unità di misura associate per ogni prodotto
        $unitaPerArticolo = $this->productUnitRepository->getUnitsByProduct();

        // 3. Recupero prezzi calcolati (con sconti e unità)
        $prezziFlat = $this->productPriceRepository->getLatestPricesForProducts($unitaPerArticolo);

        // 4. Assemblo i prodotti
        foreach ($prodotti as $prodotto) {
            $codice = $prodotto->codice;

            // Assegno unità di misura
            // $prodotto->unitaMisure = $unitaPerArticolo[$codice] ?? [];

            // Recupero tutte le voci prezzo per il prodotto
            $prezzi = $prezziFlat[$codice] ?? [];

            // Raggruppamento per listino
            $prezziPerListino = [];

            foreach ($prezzi as $entry) {
                $campiRichiesti = [
                    'codice_listino', 'listino', 'unita_misura',
                    'fattore', 'default', 'prezzo', 'sconto', 'prezzo_netto'
                ];
                $codice = $prodotto->codice ?? 'N/A';

                foreach ($campiRichiesti as $campo) {
                    if (!array_key_exists($campo, $entry)) {
                        \Log::warning("Campo mancante [$campo] per prodotto $codice", ['entry' => $entry]);
                        continue 2; // salta questa entry
                    }
                }

                $codiceListino = $entry['codice_listino'];

                if (!isset($prezziPerListino[$codiceListino])) {
                    $prezziPerListino[$codiceListino] = [
                        'listino' => $entry['listino'],
                        'voci' => [],
                    ];
                }

                $prezziPerListino[$codiceListino]['voci'][] = [
                    'unita_misura' => $entry['unita_misura'],
                    'fattore' => $entry['fattore'],
                    'default' => $entry['default'],
                    'prezzo' => $entry['prezzo'],
                    'sconto' => $entry['sconto'],
                    'prezzo_netto' => $entry['prezzo_netto'],
                ];
            }

            // Assegno i prezzi raggruppati al prodotto
            $prodotto->prezzi = $prezziPerListino;
        }

        return $prodotti;
    }

    public function getPaginated(int $perPage = 100)
    {
        $paginator = $this->productRepository->getPaginated($perPage);
        $prodotti = $paginator->getCollection();
        $codici = $prodotti->pluck('codice')->toArray();
    
        $unitaPerArticolo = $this->productUnitRepository->getUnitsByProduct();
        $prezziFlat = $this->productPriceRepository->getLatestPricesForProducts($unitaPerArticolo);
        $immaginiPerArticolo = $this->productImageRepository->getImagesByProduct($codici);
    
        foreach ($prodotti as $prodotto) {
            $codice = $prodotto->codice;
    
            // Prezzi
            $prezzi = $prezziFlat[$codice] ?? [];
            $prezziPerListino = [];
    
            foreach ($prezzi as $entry) {
                $campiRichiesti = ['codice_listino', 'listino', 'unita_misura', 'fattore', 'default', 'prezzo', 'sconto', 'prezzo_netto'];
    
                foreach ($campiRichiesti as $campo) {
                    if (!array_key_exists($campo, $entry)) {
                        \Log::warning("Campo mancante [$campo] per prodotto $codice", ['entry' => $entry]);
                        continue 2;
                    }
                }
    
                $codiceListino = $entry['codice_listino'];
    
                if (!isset($prezziPerListino[$codiceListino])) {
                    $prezziPerListino[$codiceListino] = [
                        'listino' => $entry['listino'],
                        'voci' => [],
                    ];
                }
    
                $prezziPerListino[$codiceListino]['voci'][] = [
                    'unita_misura' => $entry['unita_misura'],
                    'fattore' => $entry['fattore'],
                    'default' => $entry['default'],
                    'prezzo' => $entry['prezzo'],
                    'sconto' => $entry['sconto'],
                    'prezzo_netto' => $entry['prezzo_netto'],
                ];
            }
    
            $prodotto->prezzi = $prezziPerListino;
            $prodotto->immagini = $immaginiPerArticolo[$codice] ?? [];
        }
    
        return $paginator;
    }
      
}
