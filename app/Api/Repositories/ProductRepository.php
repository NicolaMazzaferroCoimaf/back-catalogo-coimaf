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
            // ->limit(100)
            ->get();

        return $rows->map(fn($row) => new ProductDto(
            codice: $row->Cd_AR,
            descrizione: $row->Descrizione,
            unitaMisura: $row->Cd_ARMisura,
            prezzi: [],
            // unitaMisure: []
        ))->toArray();                  
    }

    public function getPaginated(int $perPage = 100)
    {
        return DB::connection('arca')
            ->table('AR')
            ->select('Cd_AR', 'Descrizione', 'Cd_ARMisura')
            ->where('Obsoleto', 0)
            ->paginate($perPage)
            ->through(fn($row) => new ProductDto(
                codice: $row->Cd_AR,
                descrizione: $row->Descrizione,
                unitaMisura: $row->Cd_ARMisura,
                prezzi: []
            ));
    }    

}
