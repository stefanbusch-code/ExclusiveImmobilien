<?php

namespace App\Tests\Unit\Dto;

use App\Dto\PropertyFilterDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * PropertyFilterDtoTest - Unit Tests für das PropertyFilter Data Transfer Object
 *
 * Testet die Validierung der Such- und Filterparameter.
 * Stellt sicher, dass die Eingabevalidierung korrekt funktioniert
 * und Sicherheitsmechanismen gegen XSS und zu lange Eingaben greifen.
 *
 * @package App\Tests\Unit\Dto
 */
class PropertyFilterDtoTest extends KernelTestCase
{
    /**
     * Validator-Instanz für die Überprüfung der DTO-Validierung
     * Testet die Symfony Validation Constraints
     */
    private ?ValidatorInterface $validator;

    /**
     * Setup-Methode - Wird vor jedem Test ausgeführt
     * Initialisiert die Testumgebung und den Validator
     */
    protected function setUp(): void
    {
        // Symfony Kernel starten für Container-Zugriff
        self::bootKernel();

        // Validator aus Dependency Injection Container holen
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * Testet ein gültiges PropertyFilterDto mit korrekten Daten
     *
     * Szenario: Benutzer gibt valide Such- und Filterparameter ein
     * Das System sollte diese ohne Validierungsfehler akzeptieren
     *
     * Test-Ablauf:
     * 1. Erstellt DTO mit gültigen Daten
     * 2. Führt Validierung durch
     * 3. Überprüft dass keine Fehler auftreten
     */
    public function testValidDto(): void
    {
        // ===== ARRANGE Phase =====

        // DTO mit gültigen Testdaten erstellen
        $dto = new PropertyFilterDto();
        $dto->search = 'Berlin Apartment';
        $dto->preis = '100000-500000';
        $dto->town = 'Berlin';
        $dto->region = 'Berlin';
        $dto->country = 'Deutschland';

        // ===== ACT Phase =====

        // Validierung durchführen
        $errors = $this->validator->validate($dto);

        // ===== ASSERT Phase =====

        // Test: Es sollten keine Validierungsfehler auftreten
        $this->assertCount(
            0,
            $errors,
            'DTO mit gültigen Daten sollte keine Validierungsfehler haben'
        );
    }

    /**
     * Testet die Längenvalidierung für Suchbegriffe
     *
     * Szenario: Benutzer gibt einen zu langen Suchbegriff ein (>100 Zeichen)
     * Das System sollte dies mit einem Validierungsfehler ablehnen
     *
     * Test-Ablauf:
     * 1. Erstellt DTO mit zu langem Suchbegriff
     * 2. Führt Validierung durch
     * 3. Überprüft dass Fehler auftreten
     */
    public function testSearchTermTooLong(): void
    {
        // ===== ARRANGE Phase =====

        // DTO mit zu langem Suchbegriff erstellen (101 Zeichen)
        $dto = new PropertyFilterDto();
        $dto->search = str_repeat('a', 101);

        // ===== ACT Phase =====

        // Validierung durchführen
        $errors = $this->validator->validate($dto);

        // ===== ASSERT Phase =====

        // Test: Es sollten Validierungsfehler auftreten
        $this->assertGreaterThan(
            0,
            count($errors),
            'Suchbegriff mit 101 Zeichen sollte Validierungsfehler verursachen'
        );

        // Fehlermeldungen sammeln für detaillierte Prüfung
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        // Test: Fehlermeldung sollte die Maximal-Länge erwähnen
        $this->assertStringContainsString(
            '100',
            implode('', $errorMessages),
            'Fehlermeldung sollte Maximal-Länge erwähnen'
        );
    }

    /**
     * Testet die Zeichenvalidierung gegen XSS-Angriffe
     *
     * Szenario: Benutzer versucht schädliche Skripte in Suchfelder einzugeben
     * Das System sollte Sonderzeichen mit Validierungsfehler ablehnen
     *
     * Test-Ablauf:
     * 1. Erstellt DTO mit potentiell schädlichen Zeichen
     * 2. Führt Validierung durch
     * 3. Überprüft dass Fehler auftreten
     */
    public function testInvalidCharactersInSearch(): void
    {
        // ===== ARRANGE Phase =====

        // DTO mit potentiell schädlichen Zeichen erstellen
        $dto = new PropertyFilterDto();
        $dto->search = 'Test<script>alert("xss")</script>';

        // ===== ACT Phase =====

        // Validierung durchführen
        $errors = $this->validator->validate($dto);

        // ===== ASSERT Phase =====

        // Test: Es sollten Validierungsfehler auftreten
        $this->assertGreaterThan(
            0,
            count($errors),
            'Suchbegriff mit Sonderzeichen sollte Validierungsfehler verursachen'
        );
    }

    /**
     * Testet die Längenvalidierung für Städtenamen
     *
     * Szenario: Benutzer gibt einen zu langen Städtenamen ein (>100 Zeichen)
     * Das System sollte dies mit einem Validierungsfehler ablehnen
     *
     * Test-Ablauf:
     * 1. Erstellt DTO mit zu langem Städtenamen
     * 2. Führt Validierung durch
     * 3. Überprüft dass Fehler auftreten
     */
    public function testTownFieldTooLong(): void
    {
        // ===== ARRANGE Phase =====

        // DTO mit zu langem Städtenamen erstellen (101 Zeichen)
        $dto = new PropertyFilterDto();
        $dto->town = str_repeat('a', 101);

        // ===== ACT Phase =====

        // Validierung durchführen
        $errors = $this->validator->validate($dto);

        // ===== ASSERT Phase =====

        // Test: Es sollten Validierungsfehler auftreten
        $this->assertGreaterThan(
            0,
            count($errors),
            'Stadtname mit 101 Zeichen sollte Validierungsfehler verursachen'
        );
    }

    /**
     * Testet dass ein leeres DTO valide ist
     *
     * Szenario: Benutzer führt keine Suche durch (alle Felder leer)
     * Das System sollte ein leeres DTO akzeptieren, da alle Felder optional sind
     *
     * Test-Ablauf:
     * 1. Erstellt leeres DTO
     * 2. Führt Validierung durch
     * 3. Überprüft dass keine Fehler auftreten
     */
    public function testEmptyDtoIsValid(): void
    {
        // ===== ARRANGE Phase =====

        // Leeres DTO erstellen (alle Felder sind null)
        $dto = new PropertyFilterDto();

        // ===== ACT Phase =====

        // Validierung durchführen
        $errors = $this->validator->validate($dto);

        // ===== ASSERT Phase =====

        // Test: Es sollten keine Validierungsfehler auftreten
        $this->assertCount(
            0,
            $errors,
            'Leeres DTO sollte valide sein (alle Felder optional)'
        );
    }

    /**
     * Cleanup-Methode - Wird nach jedem Test ausgeführt
     *
     * Gibt Ressourcen frei und verhindert Memory-Leaks
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Validator-Referenz freigeben
        $this->validator = null;
    }
}