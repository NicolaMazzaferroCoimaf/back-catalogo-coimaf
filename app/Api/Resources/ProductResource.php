<?php

namespace App\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'codice' => $this->codice,
            'descrizione' => $this->descrizione,
            'unita_misura' => $this->unitaMisura,
            // 'unita_misure' => $this->unitaMisure,
            'prezzi' => $this->prezzi,
            'immagini' => $this->immagini,
        ];
    }
}
