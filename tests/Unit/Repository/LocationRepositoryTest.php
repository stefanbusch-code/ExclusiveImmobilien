<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Location;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * LocationRepositoryTest - Unit Tests für die LocationRepository
 *
 * Testet die standortbezogenen Datenbankabfragen für die Filter-Funktionalität.
 * Stellt sicher, dass die Dropdown-Menüs für Städte, Regionen und Länder korrekt befüllt werden.
 */
class LocationRepositoryTest extends KernelTestCase
{
    /**
     * EntityManager für Datenbankoperationen
     * Verwaltet das Persistieren und Abrufen von Location-Entitäten
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Repository für Location-Entitäten
     * Enthält die speziellen Suchmethoden für Standortdaten
     */
    private ?LocationRepository $locationRepository;

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

        // LocationRepository für Tests initialisieren
        $this->locationRepository = $this->entityManager->getRepository(Location::class);

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
        // FOREIGN KEY CHECKS ausschalten für sicheres Löschen
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        // Alle Tabellen leeren
        $tables = ['property', 'location', 'category'];
        foreach ($tables as $table) {
            $this->entityManager->getConnection()->executeStatement("DELETE FROM $table");
        }

        // FOREIGN KEY CHECKS wieder einschalten
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Testet die findDistinctTowns() Methode
     *
     * Szenario: System benötigt eine Liste aller distincten Städtenamen
     * für das Stadt-Filter-Dropdown in der Benutzeroberfläche
     *
     * Test-Ablauf:
     * 1. Erstellt Test-Standorte mit verschiedenen Städten
     * 2. Ruft die distinct Towns Methode auf
     * 3. Überprüft ob alle Städte korrekt zurückgegeben werden
     */
    public function testFindDistinctTowns(): void
    {
        // ===== ARRANGE Phase =====

        // Test-Standort 1: Berlin
        $location1 = new Location();
        $location1->setLocationTown('Berlin');
        $location1->setLocationZipcode(10115);
        $location1->setLocationStreet('Hauptstrasse');
        $location1->setLocationStreetnumber(1);
        $location1->setRegion('Berlin');
        $location1->setCountry('Deutschland');

        // Test-Standort 2: Hamburg
        $location2 = new Location();
        $location2->setLocationTown('Hamburg');
        $location2->setLocationZipcode(20095);
        $location2->setLocationStreet('Hafenstrasse');
        $location2->setLocationStreetnumber(10);
        $location2->setRegion('Hamburg');
        $location2->setCountry('Deutschland');

        // Test-Daten in Datenbank speichern
        $this->entityManager->persist($location1);
        $this->entityManager->persist($location2);
        $this->entityManager->flush();

        // ===== ACT Phase =====

        // Distinct Städtenamen aus Repository abrufen
        $towns = $this->locationRepository->findDistinctTowns();

        // ===== ASSERT Phase =====

        // Test: Rückgabe sollte ein Array sein
        $this->assertIsArray($towns, 'Rückgabe sollte ein Array sein');

        // Test: Beide Test-Städte sollten im Array enthalten sein
        $this->assertContains('Berlin', $towns, 'Berlin sollte in der Städte-Liste enthalten sein');
        $this->assertContains('Hamburg', $towns, 'Hamburg sollte in der Städte-Liste enthalten sein');

        // Test: Genau 2 Städte sollten zurückgegeben werden
        // JETZT: Sollte nur unsere 2 Test-Städte enthalten (keine alten Daten)
        $this->assertCount(2, $towns, 'Es sollten genau 2 distincte Städte zurückgegeben werden');
    }

    /**
     * Testet die findDistinctRegions() Methode
     *
     * Szenario: System benötigt eine Liste aller distincten Regionen
     * für das Regions-Filter-Dropdown in der Benutzeroberfläche
     *
     * Test-Ablauf:
     * 1. Erstellt Test-Standort mit spezifischer Region
     * 2. Ruft die distinct Regions Methode auf
     * 3. Überprüft ob die Region korrekt zurückgegeben wird
     */
    public function testFindDistinctRegions(): void
    {
        // ===== ARRANGE Phase =====

        // Test-Standort mit Region "Bayern"
        $location = new Location();
        $location->setLocationTown('München');
        $location->setLocationZipcode(80331);
        $location->setLocationStreet('Altstadt');
        $location->setLocationStreetnumber(5);
        $location->setRegion('Bayern');
        $location->setCountry('Deutschland');

        // Test-Daten in Datenbank speichern
        $this->entityManager->persist($location);
        $this->entityManager->flush();

        // ===== ACT Phase =====

        // Distinct Regionen aus Repository abrufen
        $regions = $this->locationRepository->findDistinctRegions();

        // ===== ASSERT Phase =====

        // Test: Rückgabe sollte ein Array sein
        $this->assertIsArray($regions, 'Rückgabe sollte ein Array sein');

        // Test: Bayern sollte in der Regions-Liste enthalten sein
        $this->assertContains('Bayern', $regions, 'Bayern sollte in der Regions-Liste enthalten sein');
    }

    /**
     * Testet die findDistinctCountries() Methode
     *
     * Szenario: System benötigt eine Liste aller distincten Länder
     * für das Länder-Filter-Dropdown in der Benutzeroberfläche
     *
     * Test-Ablauf:
     * 1. Erstellt Test-Standort mit spezifischem Land
     * 2. Ruft die distinct Countries Methode auf
     * 3. Überprüft ob das Land korrekt zurückgegeben wird
     */
    public function testFindDistinctCountries(): void
    {
        // ===== ARRANGE Phase =====

        // Test-Standort mit Land "Österreich"
        $location = new Location();
        $location->setLocationTown('Wien');
        $location->setLocationZipcode(1010);
        $location->setLocationStreet('Ringstrasse');
        $location->setLocationStreetnumber(1);
        $location->setRegion('Wien');
        $location->setCountry('Österreich');

        // Test-Daten in Datenbank speichern
        $this->entityManager->persist($location);
        $this->entityManager->flush();

        // ===== ACT Phase =====

        // Distinct Länder aus Repository abrufen
        $countries = $this->locationRepository->findDistinctCountries();

        // ===== ASSERT Phase =====

        // Test: Rückgabe sollte ein Array sein
        $this->assertIsArray($countries, 'Rückgabe sollte ein Array sein');

        // Test: Österreich sollte in der Länder-Liste enthalten sein
        $this->assertContains('Österreich', $countries, 'Österreich sollte in der Länder-Liste enthalten sein');
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
        $this->locationRepository = null;
    }
}