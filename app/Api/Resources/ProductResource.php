<?php

namespace App\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
            'unit' => $this->unit,
            'prices' => $this->prices,
            'images' => $this->images,
        ];
    }
}
