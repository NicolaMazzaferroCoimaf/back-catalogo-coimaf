<?php

namespace App\Api\Repositories;

use Illuminate\Support\Facades\DB;

class ProductPriceRepository
{
    public function getLatestPricesForProducts(array $unitsByProduct = []): array
    {
        // 1. Subquery: latest TimeIns per Cd_LS
        $subquery = DB::connection('arca')
            ->table('LSRevisione')
            ->selectRaw('MAX(TimeIns) as MaxTimeIns, Cd_LS')
            ->groupBy('Cd_LS');

        // 2. Join con LSRevisione per ottenere le revisioni più recenti
        $latestRevisions = DB::connection('arca')
            ->table('LSRevisione as rev')
            ->joinSub($subquery, 'latest', function ($join) {
                $join->on('rev.TimeIns', '=', 'latest.MaxTimeIns')
                     ->on('rev.Cd_LS', '=', 'latest.Cd_LS');
            })
            ->get();

        // 3. Mappa: Id_LSRevisione → Cd_LS
        $revisionsMap = $latestRevisions->pluck('Cd_LS', 'Id_LSRevisione');

        // 4. Recupera solo listini NON obsoleti
        $priceLists = DB::connection('arca')
            ->table('LS')
            ->whereIn('Cd_LS', $revisionsMap->values())
            ->where('Descrizione', '!=', 'obsoleto')
            ->pluck('Descrizione', 'Cd_LS');

        // 5. Filtra revisioni legate a listini validi
        $revisionsMap = $revisionsMap->filter(fn($cd_ls) => $priceLists->has($cd_ls));

        // 6. Recupera prezzi da LSArticolo
        $rows = DB::connection('arca')
            ->table('LSArticolo')
            ->select('Cd_AR', 'Prezzo', 'Sconto', 'Id_LSRevisione')
            ->whereIn('Id_LSRevisione', $revisionsMap->keys())
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $code = trim($row->Cd_AR);
            $listCode = $revisionsMap[$row->Id_LSRevisione] ?? null;
            $listName = $priceLists[$listCode] ?? $listCode ?? 'N/A';

            $basePrice = (float) $row->Prezzo;
            $discount = trim($row->Sconto ?? '0');
            $discountPercent = is_numeric($discount) ? (float) $discount : 0;

            $unitList = $unitsByProduct[$code] ?? [];

            foreach ($unitList as $unit) {
                $factor = $unit['factor'];
                $calculatedPrice = $basePrice * $factor;
                $netPrice = $calculatedPrice * (1 - $discountPercent / 100);

                $result[$code][] = [
                    'list_code' => $listCode,
                    'list_name' => $listName,
                    'price' => number_format($calculatedPrice, 3, '.', ''),
                    'discount' => $discount,
                    'net_price' => number_format($netPrice, 3, '.', ''),
                    'unit' => $unit['unit'],
                    'factor' => $factor,
                    'default' => $unit['default'],
                ];
            }
        }

        return $result;
    }
}