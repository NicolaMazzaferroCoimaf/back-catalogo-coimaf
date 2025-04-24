<?php

namespace App\Api\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        $price = collect($this->prices)
            ->first()['items'][0]['price'] ?? null;

        return [
            'code' => $this->code,
            'description' => $this->description,
            'images' => $this->images,
            'price' => $price,
        ];
    }
}
