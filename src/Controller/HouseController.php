<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LocationRepository;
use App\Repository\PropertyRepository;
use App\Repository\WishlistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use function Symfony\Component\String\u;

class HouseController extends AbstractController
{
    #[Route ('/house/all/{slug?}', name: 'app_house_all')]
    public function all(
        ?string $slug,
        Request $request,
        PropertyRepository $propertyRepository,
        CategoryRepository $categoryRepository,
        LocationRepository $locationRepository,
        WishlistRepository $wishlistRepository,
        AuthenticationUtils $authenticationUtils,
        RateLimiterFactory $property_search_limiter_limiter,
        RateLimiterFactory $property_filter_limiter_limiter,
        CsrfTokenManagerInterface $csrfTokenManager
    ):Response
    {
        // CSRF Token
        if($request->query->has('_token') && $this->getUser()) {
            $token = $request->query->get('_token');
            if(!$csrfTokenManager->isTokenValid(new CsrfToken('property_filters', $token))) {
                $this->addFlash('error', 'Ungültige Anfrage');
                return $this->redirectToRoute('app_house_all');
            }
        }

        $lastUsername = $authenticationUtils->getLastUsername();

        $location = $slug ? u(str_replace('-', '_', $slug))->title(true) : null;
        $category = $slug ? $categoryRepository->findOneBy(['discription' => $slug]) : null;

        $selectedTown =$request->query->get('town');
        $selectedRegion =$request->query->get('region');
        $selectedCountry =$request->query->get('country');
        $selectedPreis =$request->query->get('preis');
        $searchTerm = $request->query->get('search'); //für die Suche

        //Rate Limiter für Filter-Anfragen
        $hasAnyFilter = $request->query->get('search')
            || $request->query->get('town')
            || $request->query->get('region')
            || $request->query->get('country')
            || $request->query->get('preis');

        if ($hasAnyFilter) {
            $limiter = $property_search_limiter_limiter->create($this->getUser()?->getID() ?? $request->getClientIp());
            if(!$limiter->consume(1)->isAccepted()) {
                $this->addFlash('error','Zu viele Filter-Anfragen. Bitte warten Sie eine Minute.');
                return $this->redirectToRoute('app_house_all');
            }
        }

        //Rate Limiter für Suchanfragen
        if($searchTerm){
            $limiter = $property_search_limiter_limiter->create($this->getUser()?->getId() ?? $request->getClientIp());
            $limit = $limiter->consume(1);

            if(!$limit->isAccepted()){
                $this->addFlash('error', 'Zu viele Suchanfragen. Bitte warten Sie eine Minute');
                return $this->redirectToRoute('app_house_all',[
                    'slug' => $slug,
                    'town' => $selectedTown,
                    'region' => $selectedRegion,
                    'country' => $selectedCountry,
                    'preis' => $selectedPreis
                ]);
            }
        }

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
        if($searchTerm){
            $criteria['search'] = $searchTerm;
        }

        $properties = $propertyRepository->findByFilters($criteria, $selectedPreis);

        $towns = $locationRepository->findDistinctTowns();
        $regions = $locationRepository->findDistinctRegions();
        $countries = $locationRepository->findDistinctCountries();
        $preise = $propertyRepository->findDistinctPreise();

        $wishlistPropertyIds = [];

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
            'searchTerm' => $searchTerm, //search
            'priceRanges' => $priceRanges,
            'wishlistPropertyIds' => $wishlistPropertyIds,
            'last_username' => $lastUsername,
            'selectedCategories' => $category ? [$category->getDiscription()] : [],
            'selectedTowns' => $selectedTown ? [$selectedTown] : [],
        ]);
    }

}