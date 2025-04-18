<?php

namespace App\Api\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageRepository
{
    public function getImagesByProduct(array $codiciProdotto = []): array
    {
        $query = DB::connection('arca')
            ->table('ARImg')
            ->select('Cd_AR', 'Picture1Raw', 'Picture1OriginalFile', 'Descrizione', 'Riga')
            ->orderBy('Cd_AR')
            ->orderBy('Riga');

        if (!empty($codiciProdotto)) {
            $query->whereIn('Cd_AR', $codiciProdotto);
        }

        $rows = $query->get();
        $result = [];

        foreach ($rows as $row) {
            $cdAr = trim($row->Cd_AR);
            $riga = trim($row->Riga);
            $ext = pathinfo($row->Picture1OriginalFile, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = $cdAr . '_' . $riga . '.' . $ext;
            $path = "prodotti/$filename";

            // Salva solo se non esiste
            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $row->Picture1Raw);
            }

            $result[$cdAr][] = [
                'nome_file' => $filename,
                'url' => Storage::disk('public')->url($path),
            ];
        }

        return $result;
    }
}
