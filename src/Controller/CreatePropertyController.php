<?php

namespace App\Controller;

use App\Entity\Location;
use App\Entity\Property;
use App\Form\PropertyType;
use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PropertyRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class CreatePropertyController extends AbstractController
{
    #[Route('/createproperty', name: 'app_create_property.')]
    public function index(PropertyRepository $propertyRepository, CategoryRepository $categoryRepository, LocationRepository $locationRepository, Request $request): Response
    {
        $queryParams = $request->query->all();

        $selectedPreis = $queryParams['preis'] ?? null;
        $selectedTowns = $queryParams['towns'] ?? [];
        $selectedCategories = $queryParams['categories'] ?? [];
        $selectedCountries = $queryParams['countries'] ?? [];

        // Sicherstellen, dass Arrays vorliegen
        if (!is_array($selectedTowns)) $selectedTowns = [$selectedTowns];
        if (!is_array($selectedCategories)) $selectedTowns = [$selectedCategories];
        if (!is_array($selectedCountries)) $selectedTowns = [$selectedCountries];

        $criteria = [
            'preis' => $selectedPreis,
            'towns' => $selectedTowns,
            'categories' => $selectedCategories,
            'countries' => $selectedCountries
        ];

        $properties = $propertyRepository->findByFilters($criteria);

        $priceRanges = [
            '0 - 100.000' => [0, 100000],
            '100.000 - 500.000' => [100000, 500000],
            '500.000 - 1.000.000' => [500000, 1000000],
            '1.000.000 - 2.500.000' => [1000000, 2500000],
            '2.500.000 - 50000000' => [2500000, 5000000],
            '5.000.000+'=>[5000000, null],
        ];



        if($selectedPreis){
            list($minPreis, $maxPreis) = explode('-', $selectedPreis);
            $criteria['preis'] = [
                'min' => $minPreis,
                'max' => $maxPreis
            ];
        }
        if($selectedCategories){
            $criteria['categories'] = $selectedCategories;
        }
        if($selectedTowns){
            $criteria['towns'] = $selectedTowns;
        }
        if($selectedCountries){
            $criteria['countries'] = $selectedCountries;
        }

        $properties = $propertyRepository->findByFilters($criteria, $selectedPreis);

        $towns = $locationRepository->findDistinctTowns();
        $countries = $locationRepository->findDistinctCountries();
        $preise = $propertyRepository->findDistinctPreise();
        $categories = $categoryRepository->findAll();

        return $this->render('create_property/index.html.twig', [
            'properties' => $properties,
            'towns' => $towns,
            'categories' => $categories,
            'countries' => $countries,
            'selectedTowns' => $selectedTowns,
            'selectedCountries' => $selectedCountries,
            'selectedPreis' => $selectedPreis,
            'selectedCategories' => $selectedCategories,
            'preise' => $preise,
            'priceRanges' => $priceRanges
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
                $dateiname =md5(uniqid()) . '.'. $bild->guessClientExtension();
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
    public function show(Property $property, EntityManagerInterface $entityManager, WishlistRepository $wishlistRepository, Request $request): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        $wishlistPropertyIds = [];

        if ($user && in_array('ROLE_CUSTOMER', $user->getRoles(), true)) {
            $customer = $user->getCustomer();
            $wishlistItems = $wishlistRepository->findBy(['customer' => $customer]);
            $wishlistPropertyIds = array_map(
                fn($wishlist) => $wishlist->getProperty()->getId(),
                $wishlistItems
            );
        }

        // Filter aus Query-Parametern als Strings abrufen
        $selectedPreis = $request->query->get('preis');

        $selectedTowns = $request->query->get('towns');
        $selectedTowns = $selectedTowns ? explode(',', $selectedTowns) : [];

        $selectedCategories = $request->query->get('categories');
        $selectedCategories = $selectedCategories ? explode(',', $selectedCategories) : [];

        $selectedCountries = $request->query->get('countries');
        $selectedCountries = $selectedCountries ? explode(',', $selectedCountries) : [];

        $slug = $request->query->get('slug');

        $category = $selectedCategories[0] ?? null;

        return $this->render('create_property/show.html.twig', [
            'property' => $property,
            'wishlistPropertyIds' => $wishlistPropertyIds,
            'selectedPreis' => $selectedPreis,
            'selectedTowns' => $selectedTowns,
            'selectedCategories' => $selectedCategories,
            'selectedCountries' => $selectedCountries,
            'slug' => $slug,
            'category' => $category,
        ]);
    }

}