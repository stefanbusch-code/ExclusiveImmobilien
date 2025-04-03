<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HouseController extends AbstractController
{
    #[Route('/house/buy')]
    public function buy(): Response
    {
        $art =array('Appartment to buy','House to buy','Appartment to rent');

        return $this->render('house/buy.html.twig', [
            'a' => $art,
        ]);

    }

}