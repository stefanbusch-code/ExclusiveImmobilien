<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Property;
use App\Form\PropertyType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class CreatePropertyController extends AbstractController
{
    #[Route('/createproperty', name: 'app_create_property.')]
    public function index(PropertyRepository $pr): Response
    {
        $properties = $pr->findAll();
        return $this->render('create_property/index.html.twig', [
            'properties' => $properties,
        ]);
    }
    #[Route('/createproperty/create', name: 'app_create_property.create')]
    public function createProperty (Request $request, EntityManagerInterface $entityManager): Response
        {
            //Formular
            $property = new Property();
            $form = $this->createForm(PropertyType::class, $property);

            $form->handleRequest($request);

            //EntityManager
            if ($form->isSubmitted() && $form->isValid())
            {
                $bild = $request->files->get('property')['bild'];

                if($bild)
                {
                    $dateiname =md5(uniqid(). '.'. $bild->guessClientExtension());
                }
                $bild->move
                (
                    $this->getParameter('bilder_ordner'),
                    $dateiname
                );

                $property = $form->getData();
                $location = $property->getLocation();

                if ($location !== null) {

                    if (empty($location->getLocationTown())) {
                        $this->addFlash('error', 'Town cannot be empty.');
                        return $this->redirectToRoute('app_create_property.create');
                    }

                    if (!$location->getLocationZipcode()) {
                        $location->setLocationZipcode('Bitte ein PLZ eingeben');
                    }
                } else {
                    throw new \Exception("Location must be provided");
                }

                $property->setBild($dateiname);

                $entityManager->persist($property);
                $entityManager->flush();

                return $this->redirectToRoute('app_create_property.');
            }

            //Response
            return $this->render('create_property/createProperty.html.twig', [
                'createForm' => $form->createView(),
            ]);
        }

        #[Route('/createproperty/delete/{id}', name: 'app_create_property.delete')]
        public function deleteProperty($id, PropertyRepository $pr, EntityManagerInterface $entityManager): Response
        {
            $property = $pr->find($id);
            $entityManager->remove($property);
            $entityManager->flush();

            $this->addFlash('erfolg','Immobile wurde erfolgreich gelöscht!');

            return $this->redirectToRoute('app_create_property.');
        }

        #[Route('/createproperty/show/{id}', name: 'app_create_property.show')]
            public function show(Property $property, EntityManagerInterface $entityManager): Response
            {
                return $this->render('create_property/show.html.twig', [
                    'property' => $property,
                ]);
            }
}
