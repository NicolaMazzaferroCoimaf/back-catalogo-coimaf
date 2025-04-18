<?php

namespace App\Api\Dtos;

class ProductDto
{
    public string $codice;
    public string $descrizione;
    public ?string $unitaMisura;
    public array $prezzi = [];
    public array $immagini = [];
    // public array $unitaMisure = [];

    public function __construct(
        string $codice,
        string $descrizione,
        ?string $unitaMisura,
        array $prezzi = [],
        array $immagini = []
        // array $unitaMisure = []
    ) {
        $this->codice = trim($codice);
        $this->descrizione = trim($descrizione);
        $this->unitaMisura = trim((string) $unitaMisura);
        $this->prezzi = $prezzi;
        $this->immagini = $immagini;
        // $this->unitaMisure = $unitaMisure;
    }
}
