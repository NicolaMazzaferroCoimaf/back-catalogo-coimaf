<?php

namespace App\Api\Repositories;

use Illuminate\Support\Facades\DB;
use App\Api\Dtos\ProductDto;

class ProductRepository
{
    public function getAll(): array
    {
        $rows = DB::connection('arca')
            ->table('AR')
            ->select('Cd_AR', 'Descrizione', 'Cd_ARMisura')
            ->where('Obsoleto', 0)
            ->get();

        return $rows->map(fn($row) => new ProductDto(
            code: $row->Cd_AR,
            description: $row->Descrizione,
            unit: $row->Cd_ARMisura,
            prices: [],
            images: []
        ))->toArray();                  
    }

    public function getPaginated(int $perPage = 10)
    {
        return DB::connection('arca')
            ->table('AR')
            ->select('Cd_AR', 'Descrizione', 'Cd_ARMisura')
            ->where('Obsoleto', 0)
            ->paginate($perPage)
            ->through(fn($row) => new ProductDto(
                code: $row->Cd_AR,
                description: $row->Descrizione,
                unit: $row->Cd_ARMisura,
                prices: [],
                images: []
            ));
    }

    public function findByCode(string $code): ?ProductDto
    {
        $row = DB::connection('arca')
            ->table('AR')
            ->select('Cd_AR', 'Descrizione', 'Cd_ARMisura')
            ->where('Cd_AR', $code)
            ->where('Obsoleto', 0)
            ->first();

        if (!$row) return null;

        return new ProductDto(
            code: $row->Cd_AR,
            description: $row->Descrizione,
            unit: $row->Cd_ARMisura,
            prices: [],
            images: []
        );
    }
}