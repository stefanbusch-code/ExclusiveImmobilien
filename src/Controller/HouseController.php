<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;

class HouseController extends AbstractController
{
    #[Route ('/house/all/{slug?}', name: 'app_house_all')]
    public function all(?string $slug, PropertyRepository $propertyRepository, CategoryRepository $categoryRepository, LocationRepository $locationRepository):Response
    {
        $location = $slug ? u(str_replace('-', '_', $slug))->title(true) : null;
        $category = $slug ? $categoryRepository->findOneBy(['discription' => $slug]) : null;

        $properties = $category
            ? $propertyRepository->findBy(['category' => $category])
            : $propertyRepository->findAll();

        return $this->render('house/all.html.twig', [
            'properties' => $properties,
            'location' => $location,
            'category' => $category,
        ]);
    }


}