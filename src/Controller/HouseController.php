<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PropertyRepository;
use App\Repository\WishlistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;

class HouseController extends AbstractController
{
    #[Route ('/house/all/{slug?}', name: 'app_house_all')]
    public function all(?string $slug, Request $request, PropertyRepository $propertyRepository, CategoryRepository $categoryRepository, LocationRepository $locationRepository, WishlistRepository $wishlistRepository):Response
    {
        $location = $slug ? u(str_replace('-', '_', $slug))->title(true) : null;
        $category = $slug ? $categoryRepository->findOneBy(['discription' => $slug]) : null;

        $selectedTown =$request->query->get('town');
        $selectedRegion =$request->query->get('region');
        $selectedCountry =$request->query->get('country');
        $selectedPreis =$request->query->get('preis');

        $priceRanges = [
            '0 - 100.000' => [0, 100000],
            '100.000 - 500.000' => [100000, 500000],
            '500.000 - 1.000.000' => [500000, 1000000],
            '1.000.000 - 2.500.000' => [1000000, 2500000],
            '2.500.000 - 50000000' => [2500000, 5000000],
            '5.000.000+'=>[5000000, null],
        ];

        $criteria = [];

        if($category){
            $criteria['category'] = $category;
        }
        if($selectedTown){
            $criteria['location_town'] = $selectedTown;
        }
        if($selectedRegion){
            $criteria['region'] = $selectedRegion;
        }
        if($selectedCountry){
            $criteria['country'] = $selectedCountry;
        }
        if($selectedPreis){
            list($minPreis, $maxPreis) = explode('-', $selectedPreis);
            $criteria['preis'] = [
                'min' => $minPreis,
                'max' => $maxPreis
            ];
        }

        $properties = $propertyRepository->findByFilters($criteria, $selectedPreis);

        $towns = $locationRepository->findDistinctTowns();
        $regions = $locationRepository->findDistinctRegions();
        $countries = $locationRepository->findDistinctCountries();
        $preise = $propertyRepository->findDistinctPreise();

        if ($this->getUser()){
            $customer = $this->getUser()->getCustomer();
            $wishlistItems = $wishlistRepository->findBy(['customer' => $customer]);
            $wishlistPropertyIds = array_map(fn($item)=>$item->getProperty()->getId(), $wishlistItems);
        }

        return $this->render('house/all.html.twig', [
            'properties' => $properties,
            'location' => $location,
            'category' => $category,
            'towns' => $towns,
            'regions' => $regions,
            'countries' => $countries,
            'preise' => $preise,
            'selectedTown' => $selectedTown,
            'selectedRegion' => $selectedRegion,
            'selectedCountry' => $selectedCountry,
            'selectedPreis' => $selectedPreis,
            'priceRanges' => $priceRanges,
            'wishlistPropertyIds' => $wishlistPropertyIds,
        ]);
    }

}