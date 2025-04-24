<?php

namespace App\Api\Dtos;

class ProductDto
{
    public string $code;
    public string $description;
    public ?string $unit;
    public array $prices = [];
    public array $images = [];

    public function __construct(
        string $code,
        string $description,
        ?string $unit,
        array $prices = [],
        array $images = []
    ) {
        $this->code = trim($code);
        $this->description = trim($description);
        $this->unit = trim((string) $unit);
        $this->prices = $prices;
        $this->images = $images;
    }
}
