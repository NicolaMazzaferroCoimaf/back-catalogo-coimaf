<?php

namespace App\Api\Repositories;

use Illuminate\Support\Facades\DB;

class ProductPriceRepository
{
    public function getLatestPricesForProducts(array $unitaPerProdotto = []): array
    {
        // 1. Subquery: ultima TimeIns per ogni Cd_LS
        $subquery = DB::connection('arca')
            ->table('LSRevisione')
            ->selectRaw('MAX(TimeIns) as MaxTimeIns, Cd_LS')
            ->groupBy('Cd_LS');

        // 2. Join con LSRevisione e recupero revisioni recenti
        $latestRevisions = DB::connection('arca')
            ->table('LSRevisione as rev')
            ->joinSub($subquery, 'latest', function ($join) {
                $join->on('rev.TimeIns', '=', 'latest.MaxTimeIns')
                     ->on('rev.Cd_LS', '=', 'latest.Cd_LS');
            })
            ->get();

        // 3. Mappa Id_LSRevisione → Cd_LS
        $revisionsMap = $latestRevisions->pluck('Cd_LS', 'Id_LSRevisione');

        // 4. Recupero i listini NON obsoleti
        $listini = DB::connection('arca')
            ->table('LS')
            ->whereIn('Cd_LS', $revisionsMap->values())
            ->where('Descrizione', '!=', 'obsoleto')
            ->pluck('Descrizione', 'Cd_LS');

        // 5. Rimuovo i listini obsoleti dal revisionsMap
        $revisionsMap = $revisionsMap->filter(fn($cd_ls) => $listini->has($cd_ls));

        // 6. Recupero prezzi da LSArticolo
        $rows = DB::connection('arca')
            ->table('LSArticolo')
            ->select('Cd_AR', 'Prezzo', 'Sconto', 'Id_LSRevisione')
            ->whereIn('Id_LSRevisione', $revisionsMap->keys())
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $codice = trim($row->Cd_AR);
            $cd_ls = $revisionsMap[$row->Id_LSRevisione] ?? null;
            $listinoNome = $listini[$cd_ls] ?? $cd_ls ?? 'N/D';

            $prezzoBase = (float) $row->Prezzo;
            $sconto = trim($row->Sconto ?? '0');
            $scontoPercentuale = is_numeric($sconto) ? (float) $sconto : 0;

            $unitList = $unitaPerProdotto[$codice] ?? [];

            foreach ($unitList as $unit) {
                $fattore = $unit['fattore'];
                $prezzoCalcolato = $prezzoBase * $fattore;
                $prezzoNetto = $prezzoCalcolato * (1 - $scontoPercentuale / 100);

                $result[$codice][] = [
                    'codice_listino' => $cd_ls,
                    'listino' => $listinoNome,
                    'prezzo' => number_format($prezzoCalcolato, 3, '.', ''),
                    'sconto' => $sconto,
                    'prezzo_netto' => number_format($prezzoNetto, 3, '.', ''),
                    'unita_misura' => $unit['unita_misura'],
                    'fattore' => $fattore,
                    'default' => $unit['default'],
                ];
            }
        }

        return $result;
    }
}