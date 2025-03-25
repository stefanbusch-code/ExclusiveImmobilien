<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;

class ImmobilienController extends AbstractController
{
    #[Route('/')]
    public function homepage()
    {
        $Immobilien =
            [
                ['art' => 'Eigentumswohnung', 'ort' => 'Warnemünde'],
                ['art' => 'Eigentumswohnung', 'ort' => 'Börgerende'],
                ['art' => 'Haus zur Miete', 'ort' => 'Nienhagen'],
                ['art' => 'Haus zur Miete', 'ort' => 'Kühlungsborn'],
                ['art' => 'Haus zum Kauf', 'ort' => 'Kühlungsborn'],
                ['art' => 'Haus zum Kauf', 'ort' => 'Warnemünde'],
            ];
        return $this->render('immobilien/homepage.html.twig',
        [
            'title' => 'Exclusive Immobilien',
            'immobilien' => $Immobilien,
        ]);

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

