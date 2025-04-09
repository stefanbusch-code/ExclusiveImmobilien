<?php

namespace App\Controller;


use App\Repository\LocationRepository;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;

class PropertyController extends AbstractController
{

    /*
    #[Route('/', name: 'app_property_homepage')]
    public function homepage(PropertyRepository $propertyRepository): Response
    {
        $property =
            $propertyRepository->findAll();
        return $this->render('immobilien/homepage.html.twig',
            [
                'title' => 'Different properties for your wellbeing!',
                'properties' => $property,
            ]);

    }*/


    #[Route('/',name: 'app_property_homepage')]
    public function index(PropertyRepository $propertyRepository):Response
    {
        $properties = $propertyRepository->findRandomProperties(3);

        return $this->render('immobilien/homepage.html.twig', [
            'properties' => $properties,
        ]);
    }



    #[Route('/SeeAllProperties/{slug}', name: 'app_property_seeallproperties')]
    public function SeeAllProperties($slug=null):Response
    {

        $title =$slug ? u(str_replace('-', ' ', $slug))->title(true) : null;

        return $this->render('immobilien/SeeAllProperies.html.twig', [
            'title' => $title,
        ]);

    }
}

