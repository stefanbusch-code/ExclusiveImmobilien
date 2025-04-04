<?php

namespace App\Controller;

use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;

class HouseController extends AbstractController
{
    #[Route('/house/buy/{slug}')]
    public function buy(string $slug =null): Response
    {
        $location = $slug ? u(str_replace('-', ' ', $slug))->title(true) : null;

        return $this->render('house/buy.html.twig', [
            'location' => $location,
        ]);

    }
    #[Route('/house/all', name: 'app_property_all')]
    public function property(PropertyRepository $propertyRepository): Response
    {
        $properties = $propertyRepository->findAll();

        return $this->render('house/all.html.twig', [
            'properties' => $properties,
        ]);

    }

}