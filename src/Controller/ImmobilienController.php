<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;

class ImmobilienController
{
    #[Route('/')]
    public function homepage()
    {
        return new Response('Exclusive Immobilien');
    }

    #[Route('/show/{slug}')]
    public function show($slug=null):Response
    {
        if ($slug)
        {
            $title = 'Immobilienart: '.u(str_replace('-', ' ', $slug))->title(true);
        }
        else
        {
            $title = 'Alle immobilien';
        }

        return new Response($title);

    }
}

