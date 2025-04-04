<?php

namespace App\Controller;


use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;

class PropertyController extends AbstractController
{
    #[Route('/', name: 'app_property_homepage')]
    public function homepage(LocationRepository $locationRepository): Response
    {
        $immobilien =
            $locationRepository->findAll();
        return $this->render('immobilien/homepage.html.twig',
            [
                'title' => 'Different properties for your wellbeing!',
                'immobilien' => $immobilien,
            ]);

    }

    #[Route('/SeeAllProperies/{slug}')]
    public function SeeAllProperties($slug=null):Response
    {

        $title =$slug ? u(str_replace('-', ' ', $slug))->title(true) : null;

        return $this->render('immobilien/SeeAllProperies.html.twig', [
            'title' => $title,
        ]);

    }
}

