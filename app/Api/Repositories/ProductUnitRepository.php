<?php

namespace App\Api\Repositories;

use Illuminate\Support\Facades\DB;

class ProductUnitRepository
{
    public function getUnitsByProduct(): array
    {
        $rows = DB::connection('arca')
            ->table('ARARMisura')
            ->select('Cd_AR', 'Cd_ARMisura', 'UMFatt', 'DefaultMisura')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $result[trim($row->Cd_AR)][] = [
                'unita_misura' => trim($row->Cd_ARMisura),
                'fattore' => (float) $row->UMFatt,
                'default' => $row->DefaultMisura == 1
            ];
        }

        return $result;
    }
}
