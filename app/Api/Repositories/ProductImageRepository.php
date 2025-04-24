<?php

namespace App\Api\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageRepository
{
    public function getImagesByProduct(array $productCodes = []): array
    {
        $query = DB::connection('arca')
            ->table('ARImg')
            ->select('Cd_AR', 'Picture1Raw', 'Picture1OriginalFile', 'Descrizione', 'Riga')
            ->orderBy('Cd_AR')
            ->orderBy('Riga');

        if (!empty($productCodes)) {
            $query->whereIn('Cd_AR', $productCodes);
        }

        $rows = $query->get();
        $result = [];

        foreach ($rows as $row) {
            $code = trim($row->Cd_AR);
            $rowNumber = trim($row->Riga);
            $ext = pathinfo($row->Picture1OriginalFile, PATHINFO_EXTENSION) ?: 'jpg';
            $filename = $code . '_' . $rowNumber . '.' . $ext;
            $path = "products/$filename";

            if (!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $row->Picture1Raw);
            }

            $result[$code][] = [
                'file_name' => $filename,
                'url' => Storage::disk('public')->url($path),
            ];
        }

        return $result;
    }
}
