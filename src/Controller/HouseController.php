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
use App\Dto\PropertyFilterDto;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * HouseController - steuert die Immobilien-Suche und Filterfunktionen
 *
 * Verantwortlich für die Darstellung der Immobilien-Übersicht,
 * Verarbeitung von Suchanfragen und Filterung sowie die Detailansicht.
 */
class HouseController extends AbstractController
{
    /**
     * Zeigt alle Immobilien mit Filter- und Suchfunktionen an
     *
     * Verarbeitet Suchparameter, validiert Eingaben, wendet Filter an
     * und zeigt die Ergebnisse in einer Grid-basierten Übersicht.
     *
     * @param string|null $slug kategorie-Slug für vorgefilterte Ansicht (z.B. 'Apartments-zum-kaufen')
     * @param Request $request HTTP Request mit Suchparameter
     * @param PropertyRepository $propertyRepository Repository für Immobilien-Datenbankzugriffe
     * @param CategoryRepository $categoryRepository Repository für Category-Datenbankzugriffe
     * @param LocationRepository $locationRepository Repository für Standort-Datenbankzugriffe
     * @param WishlistRepository $wishlistRepository Repository für Merklisten-Funktionalität
     * @param AuthenticationUtils $authenticationUtils Zur Authentifizierungs-Information
     * @param RateLimiterFactory $property_search_limiter_limiter Zugriffsanfragenbegrenzung für Suchfunktion
     * @param RateLimiterFactory $property_filter_limiter_limiter Zugriffsanfragenbegrenzung für Filterfunktion
     * @param CsrfTokenManagerInterface $csrfTokenManager Schutz vor manipulierten Formular-Absendungen (CSRF-Schutz)
     * @param ValidatorInterface $validator Validator für Eingabevalidierung
     * @return Response gerendertes Twig Template mit Immobilien-Daten
     */

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
        CsrfTokenManagerInterface $csrfTokenManager,
        ValidatorInterface $validator,
    ):Response
    {
        // CSRF Token Validation für authentifizierte Benutzer

        $hasFilterParams =
            $request->query->has('search') ||
            $request->query->has('town') ||
            $request->query->has('region') ||
            $request->query->has('country') ||
            $request->query->has('preis') ||
            $request->query->has('slug');

        if ($this->getUser() && $hasFilterParams) {

            if (!$request->query->has('_token')) {
                $this->addFlash('error', 'Ungültige Anfrage (CSRF Token fehlt).');
                return $this->redirectToRoute('app_house_all');
            }

            $token = $request->query->get('_token');

            if (!$csrfTokenManager->isTokenValid(new CsrfToken('property_filters', $token))) {
                $this->addFlash('error', 'Ungültige Anfrage (CSRF Token ungültig).');
                return $this->redirectToRoute('app_house_all');
            }
        }

        // Eingabeprüfung mit DTO und Validator

        $dto = new PropertyFilterDto();
        $dto->search = $request->query->get('search');
        $dto->preis = $request->query->get('preis');
        $dto->town = $request->query->get('town');
        $dto->region = $request->query->get('region');
        $dto->country = $request->query->get('country');

        $errors = $validator->validate($dto);

        if(count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage()));
            }

            return $this->redirectToRoute('app_house_all');
        }

        //Vorbereitung der Parameter für Template-Übergabe
        $selectedPreis = $dto->preis;
        $selectedTown = $dto->town;
        $selectedRegion = $dto->region;
        $selectedCountry = $dto->country;
        $searchTerm = $dto->search;

        $lastUsername = $authenticationUtils->getLastUsername();

        // Kategorie und Standort-Informationen aus slug ermitteln

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
            $limiter = $property_filter_limiter_limiter->create($this->getUser()?->getID() ?? $request->getClientIp());
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

        // Preisbereichsdefinition für Dropdown-Menü
        $priceRanges = [
            '0 - 99.999' => [0, 99999],
            '100.000 - 499.999' => [100000, 499999],
            '500.000 - 999.999' => [500000, 999999],
            '1.000.000 - 2.499.999' => [1000000, 2499999],
            '2.500.000 - 4.999.999' => [2500000, 4999999],
            '5.000.000+'=>[5000000, null],
        ];

        // Zusammenstellung der Filterkriterien für repository-Abfragen
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

        // Immobilien mit angewendeten Filter abrufen
        $properties = $propertyRepository->findByFilters($criteria, $selectedPreis);

        $towns = $locationRepository->findDistinctTowns();
        $regions = $locationRepository->findDistinctRegions();
        $countries = $locationRepository->findDistinctCountries();
        $preise = $propertyRepository->findDistinctPreise();

        //Merklisten-IDs für authentifizierte Benutzer laden
        $wishlistPropertyIds = [];

        if ($this->getUser()){
            $customer = $this->getUser()->getCustomer();
            $wishlistItems = $wishlistRepository->findBy(['customer' => $customer]);
            $wishlistPropertyIds = array_map(fn($item)=>$item->getProperty()->getId(), $wishlistItems);
        }

        // Template mit allen Daten rendern
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