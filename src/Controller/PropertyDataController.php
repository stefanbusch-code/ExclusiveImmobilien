<?php

namespace App\Controller;

use App\Entity\Property;
use App\Form\PropertyEditType;
use App\Form\PropertyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class PropertyDataController extends AbstractController
{
    #[Route('/property/edit/{id}', name: 'app_property_edit')]
    public function editProperty(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $property = $entityManager->getRepository(Property::class)->find($id);

        if(!$property) {
            throw $this->createNotFoundException('Property nicht gefunden');
        }

        $propertyForm = $this->createForm(PropertyEditType::class, $property);
        $propertyForm->handleRequest($request);

        if($propertyForm->isSubmitted()&& $propertyForm->isValid()) {

            $bild = $propertyForm->get('bild')->getData();


            if ($bild) {
                $dateiname = md5(uniqid()) . '.' . $bild->guessClientExtension();
                $bild->move($this->getParameter('bilder_ordner'), $dateiname);
                $property->setBild($dateiname);
            } else {
                // altes Bild erhalten (falls nötig)
                $existing = $entityManager->getUnitOfWork()->getOriginalEntityData($property);
                if (isset($existing['bild'])) {
                    $property->setBild($existing['bild']);
                }
            }



            $location = $property->getLocation();

            $newPropertyTitle = $propertyForm->get('property_title')->getData();
            if (!empty($newPropertyTitle)) {
                $property->setPropertyTitle($newPropertyTitle);
            }

            $newPropertyDiscription = $propertyForm->get('property_discription')->getData();
            if(!empty($newPropertyDiscription)) {
                $property->setPropertyDiscription($newPropertyDiscription);
            }

            $newPropertyPrice = $propertyForm->get('preis')->getData();
            if (!empty($newPropertyPrice)) {
                $property->setPreis($newPropertyPrice);
            }

            if ($location) {

                $newLocationZipcode = $propertyForm->get('location')->get('location_zipcode')->getData();
                if (!empty($newLocationZipcode)) {
                    $location->setLocationZipcode($newLocationZipcode);
                }

                $newLocationTown = $propertyForm->get('location')->get('location_town')->getData();
                if (!empty($newLocationTown)) {
                    $location->setLocationTown($newLocationTown);
                }

                $newLocationStreet = $propertyForm->get('location')->get('location_street')->getData();
                if (!empty($newLocationStreet)) {
                    $location->setLocationStreet($newLocationStreet);
                }

                $newLocationStreetnumber = $propertyForm->get('location')->get('location_streetnumber')->getData();
                if (!empty($newLocationStreetnumber)) {
                    $location->setLocationStreetnumber($newLocationStreetnumber);
                }

                $newLocationRegion = $propertyForm->get('location')->get('region')->getData();
                if (!empty($newLocationRegion)) {
                    $location->setRegion($newLocationRegion);
                }

                $newLocationCountry = $propertyForm->get('location')->get('country')->getData();
                if (!empty($newLocationCountry)) {
                    $location->setCountry($newLocationCountry);
                }
            }

            $newCategorie = $propertyForm->get('category')->getData();
            if (!empty($newCategorie)){
                $property->setCategory($newCategorie);
            }

            $entityManager->persist($property);
            $entityManager->flush();

            $this->addFlash('success', 'Immobilie erfolgreich aktualisiert');
            return $this->redirectToRoute('app_create_property.');
        }

        return $this->render('property/edit.html.twig', [
            'propertyForm' => $propertyForm -> createView(),
        ]);

    }


}