<?php

namespace App\Controller;

use App\Entity\Location;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LocationController extends AbstractController
{
    #[Route('/location', name: 'create_location')]
    public function createLocation(EntityManagerInterface $entityManager): Response
    {
        $location = new Location();
        $location->setLocationZipcode('18109');
        $location->setLocationTown('Warnemünde');
        $location->setLocationStreet('Am Strom');
        $location->setLocationStreetnumber('1');

        dd($location);

        $entityManager->persist($location);
        $entityManager->flush();

        return new Response('Saved new location with id '.$location->getId());
    }
}
