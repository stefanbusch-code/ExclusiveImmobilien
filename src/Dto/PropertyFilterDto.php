<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * sammelt alle filtereingaben und validiert mithilfe von symfony-validator-komponente
 */

class PropertyFilterDto
{
    #[Assert\Length(max: 100, maxMessage: 'Suchbegriffe dürfen max 100 Zeichen lang sein')]
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{N}\s\-]*$/u',
        message: 'Nur Buchstaben, Zahlen und leerzeichen erlaubt.'
    )]
    public ?string $search = null;

    #[Assert\Regex(
        pattern: '/^[\p{L}\p{N}\s\-]*$/u',
        message: 'Ungültiges Preisformat. Beispiel: 1000000-5000000'
    )]
    public ?string $preis = null;

    #[Assert\Length(max: 100, maxMessage: 'Städtenamen dürfen maximal 100 Zeichen lang sein.')]
    public ?string $town = null;

    #[Assert\Length(max: 100, maxMessage: 'Regionsbezeichnungen dürfen maximal 100 Zeichen lang sein.')]
    public ?string $region = null;

    #[Assert\Length(max: 100, maxMessage: 'Ländernamen dürfen maximal 100 Zeichen lang sein.')]
    public ?string $country = null;

}