<?php

namespace App\Api\Dtos;

class ProductDto
{
    public string $codice;
    public string $descrizione;
    public ?string $unitaMisura;
    public array $prezzi = [];
    public array $unitaMisure = [];

    public function __construct(
        string $codice,
        string $descrizione,
        ?string $unitaMisura,
        array $prezzi = [],
        array $unitaMisure = []
    ) {
        $this->codice = trim($codice);
        $this->descrizione = trim($descrizione);
        $this->unitaMisura = trim((string) $unitaMisura);
        $this->prezzi = $prezzi;
        $this->unitaMisure = $unitaMisure;
    }
}
