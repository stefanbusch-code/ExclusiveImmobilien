<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Category;
use App\Entity\Location;
use App\Entity\Property;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Unit Tests für die PropertyRepository
 *
 * Testet die Kernfunktionalitäten der Immobilien-Suche und -Filterung.
 * Stellt sicher, dass die Suchalgorithmen, Preis-Filterung und Datenbankabfragen
 * korrekt funktionieren und die erwarteten Ergebnisse liefern.
 */
class PropertyRepositoryTest extends KernelTestCase
{
    /**
     * EntityManager für Datenbankoperationen
     * Verwaltet das Persistieren und Abrufen von Property-Entitäten
     * und deren Beziehungen zu Location und Category
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Repository für Property-Entitäten
     * Enthält die speziellen Such- und Filtermethoden für das Immobilienportfolio
     */
    private ?PropertyRepository $propertyRepository;

    /**
     * Setup-Methode - Wird vor jedem Test ausgeführt
     * Initialisiert die Testumgebung und bereitet die Datenbank vor
     */
    protected function setUp(): void
    {
        // Symfony Kernel starten für Container-Zugriff
        self::bootKernel();

        // EntityManager aus Dependency Injection Container holen
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();

        // PropertyRepository für Tests initialisieren
        $this->propertyRepository = $this->entityManager->getRepository(Property::class);

        // WICHTIG: Datenbank vor jedem Test bereinigen
        $this->cleanDatabase();
    }

    /**
     * Bereinigt die Datenbank vor jedem Test
     * Entfernt alle vorhandenen Property-, Location- und Category-Einträge
     * für isolierte Tests
     */
    private function cleanDatabase(): void
    {
        // FOREIGN KEY CHECKS ausschalten
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        $tables = ['property', 'location', 'category'];
        foreach ($tables as $table) {
            $this->entityManager->getConnection()->executeStatement("DELETE FROM $table");
        }

        // FOREIGN KEY CHECKS wieder einschalten
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Testet die findByFilters() Methode mit Suchbegriff
     *
     * Szenario: Benutzer sucht nach Immobilien mit dem Begriff "Berlin"
     * Das System sollte alle Immobilien finden, die "Berlin" im Titel,
     * der Beschreibung, dem Standort oder der Kategorie enthalten
     *
     * Test-Ablauf:
     * 1. Erstellt Test-Immobilie mit Berlin-Bezug
     * 2. Führt Repository-Suche mit Suchbegriff aus
     * 3. Überprüft ob die Immobilie korrekt gefunden wird
     */
    public function testFindByFiltersWithSearch(): void
    {
        // ====== ARRANGE PHASE =====

        // Test-Daten vorbereiten

        // Location-Entity für Berlin erstellen
        $location = new Location();
        $location->setLocationTown('Berlin');
        $location->setLocationZipcode(10115);
        $location->setLocationStreet('Hauptstrasse');
        $location->setLocationStreetnumber(1);
        $location->setRegion('Berlin');
        $location->setCountry('Deutschland');

        // Category-Entity erstellen
        $category = new Category();
        $category->setDiscription('Apartment');

        // Property-Entity mit Berlin-Bezug erstellen
        $property = new Property();
        $property->setPropertyTitle('Luxus Apartment Berlin Mitte');
        $property->setPropertyDiscription('Tolles Apartment in bester Lage');
        $property->setPreis(500000);
        $property->setBild('berlin.jpg');
        $property->setLocation($location);
        $property->setCategory($category);

        // Entities in der Datenbank speichern
        $this->entityManager->persist($location);
        $this->entityManager->persist($category);
        $this->entityManager->persist($property);
        $this->entityManager->flush();

        // ====== ACT Phase ======

        // Suchkriterien definieren - Suche nach "Berlin"
        $criteria = ['search' => 'Berlin'];

        // Repository-Suchmethode aufrufen
        $results = $this->propertyRepository->findByFilters($criteria);

        // ===== ASSERT Phase ======

        // Test: Es sollte genau eine Immobilie gefunden werden
        $this->assertCount(
            1,
            $results,
            'Es sollte genau eine Immobilie mit Suchbegriff "Berlin" gefunden werden'
        );

        // Test: Der Titel der gefundenen Immobilie sollte korrekt sein
        $this->assertEquals(
            'Luxus Apartment Berlin Mitte',
            $results[0]->getPropertyTitle(),
            'Property-Titel sollte mit dem erwarteten Wert übereinstimmen'
        );
    }

    /**
     * Testet die findByFilters() Methode mit Preisbereich-Filter
     *
     * Szenario: Benutzer filtert Immobilien nach Preisbereich 0 - 499.000 €
     * Das System sollte nur Immobilien zurückgeben, deren Preis innerhalb
     * des definierten Bereichs liegt
     *
     * Test-Ablauf:
     * 1. Erstellt Test-Immobilie mit Preis 300.000 €
     * 2. Führt Repository-Filter mit Preisbereich aus
     * 3. Überprüft ob die Immobilie korrekt gefiltert wird
     */
    public function testFindByFiltersPriceRange(): void
    {
        // ===== ARRANGE Phase ======

        // Location für Test-Immobilie in Hamburg
        $location = new Location();
        $location->setLocationTown('Hamburg');
        $location->setLocationZipcode(20095);
        $location->setLocationStreet('Hafenstrasse');
        $location->setLocationStreetnumber(10);
        $location->setRegion('Hamburg');
        $location->setCountry('Deutschland');

        // Category für Test-Immobilie
        $category = new Category();
        $category->setDiscription('Haus');

        // Property mit Preis 300.000 € (innerhalb des Filterbereichs)
        $property = new Property();
        $property->setPropertyTitle('Wohnhaus Hamburg');
        $property->setPropertyDiscription('Schoenes Haus am Hafen');
        $property->setPreis(300000);
        $property->setBild('hamburg.jpg');
        $property->setLocation($location);
        $property->setCategory($category);

        // Test-Daten in Datenbank speichern
        $this->entityManager->persist($location);
        $this->entityManager->persist($category);
        $this->entityManager->persist($property);
        $this->entityManager->flush();

        // ===== ACT Phase =====

        // Preisbereich-Filter definieren: 0 - 499.000 €
        $criteria = [
            'preis' => [
                'min' => 0,     // Untergrenze: 0 €
                'max' => 499000 // Obergrenze: 499.000 €
            ]
        ];

        // Repository-Filter mit Preisbereich aufrufen
        $results = $this->propertyRepository->findByFilters($criteria);

        // ===== ASSERT Phase =====

        // Test: Es sollte genau eine Immobilie im Preisbereich gefunden werden
        $this->assertCount(
            1,
            $results,
            'Es sollte genau eine Immobilie im Preisbereich 0-499.000 € gefunden werden'
        );

        // Test: Der Preis der gefundenen Immobilie sollte 300.000 € betragen
        $this->assertEquals(
            300000,
            $results[0]->getPreis(),
            'Der Preis der gefundenen Immobilie sollte 300.000 € betragen'
        );
    }

    /**
     * Testet die findDistinctPreise() Methode
     *
     * Szenario: System benötigt eine Liste aller distincten Preiswerte
     * für erweiterte Filteroptionen oder Admin-Funktionen
     *
     * Test-Ablauf:
     * 1. Erstellt Test-Immobilie mit spezifischem Preis
     * 2. Ruft die distinct Preise Methode auf
     * 3. Überprüft ob der Preis in der Liste enthalten ist
     */
    public function testFindDistinctPreise(): void
    {
        // ===== ARRANGE Phase =====

        // Location für Test-Immobilie
        $location = new Location();
        $location->setLocationTown('Teststadt');
        $location->setLocationZipcode(12345);
        $location->setLocationStreet('Teststrasse');
        $location->setLocationStreetnumber(1);
        $location->setRegion('Testregion');
        $location->setCountry('Deutschland');

        // Category für Test-Immobilie
        $category = new Category();
        $category->setDiscription('Testkategorie');

        // Property mit spezifischem Preis
        $property = new Property();
        $property->setPropertyTitle('Test Immobilie');
        $property->setPropertyDiscription('Test Beschreibung');
        $property->setPreis(500000);
        $property->setBild('test.jpg');
        $property->setLocation($location);
        $property->setCategory($category);

        // Test-Daten in Datenbank speichern
        $this->entityManager->persist($location);
        $this->entityManager->persist($category);
        $this->entityManager->persist($property);
        $this->entityManager->flush();

        // ===== ACT Phase =====

        // Distinct Preise aus Repository abrufen
        $preise = $this->propertyRepository->findDistinctPreise();

        // ===== ASSERT Phase =====

        // Test: Rückgabe sollte ein Array sein
        $this->assertIsArray(
            $preise,
            'Rückgabe der distincten Preise sollte ein Array sein'
        );

        // Test: Das Array sollte nicht leer sein
        $this->assertNotEmpty(
            $preise,
            'Die Liste der distincten Preise sollte nicht leer sein'
        );

        // Test: Der Test-Preis sollte im Array enthalten sein
        // (kann als Integer oder String zurückgegeben werden)
        $hasPrice = in_array(500000, $preise) || in_array('500000', $preise);
        $this->assertTrue(
            $hasPrice,
            'Preis 500000 sollte in distinct Preise enthalten sein'
        );
    }

    /**
     * Cleanup-Methode - Wird nach jedem Test ausgeführt
     *
     * Stellt den ursprünglichen Zustand wieder her:
     * - Schließt den EntityManager
     * - Gibt Ressourcen frei
     * - Verhindert Memory-Leaks zwischen Tests
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // EntityManager schließen
        $this->entityManager->close();

        // Referenzen auf null setzen für Garbage Collection
        $this->entityManager = null;
        $this->propertyRepository = null;
    }
}