<?php

namespace App\Tests\Unit\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * CategoryRepositoryTest - Unit Tests für die CategoryRepository
 *
 * Testet die grundlegenden Datenbankoperationen für Kategorie-Entitäten.
 * Stellt sicher, dass Kategorien korrekt erstellt, gespeichert, abgerufen
 * und verwaltet werden können.
 *
 * @package App\Tests\Unit\Repository
 */
class CategoryRepositoryTest extends KernelTestCase
{
    /**
     * EntityManager für Datenbankoperationen
     * Verwaltet das Persistieren und Abrufen von Category-Entitäten
     * und koordiniert Datenbank-Transaktionen
     */
    private ?EntityManagerInterface $entityManager;

    /**
     * Repository für Category-Entitäten
     * Enthält die Standard-Doctrine-Methoden für Kategorie-Operationen
     * sowie eventuelle benutzerdefinierte Suchmethoden
     */
    private ?CategoryRepository $categoryRepository;

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

        // CategoryRepository für Tests initialisieren
        $this->categoryRepository = $this->entityManager->getRepository(Category::class);

        // WICHTIG: Datenbank vor jedem Test bereinigen
        $this->cleanDatabase();
    }

    /**
     * Bereinigt die Datenbank vor jedem Test
     * Entfernt alle vorhandenen Property-, Location- und Category-Einträge
     * für isolierte und reproduzierbare Tests
     */
    private function cleanDatabase(): void
    {
        // FOREIGN KEY CHECKS ausschalten für sicheres Löschen
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        // Alle Tabellen leeren um konsistente Testbedingungen zu gewährleisten
        $tables = ['property', 'location', 'category'];
        foreach ($tables as $table) {
            $this->entityManager->getConnection()->executeStatement("DELETE FROM $table");
        }

        // FOREIGN KEY CHECKS wieder einschalten
        $this->entityManager->getConnection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Testet die Erstellung und den Abruf einer Kategorie
     *
     * Szenario: System erstellt eine neue Immobilien-Kategorie
     * und muss diese später korrekt aus der Datenbank abrufen können.
     * Dies testet den kompletten CRUD-Zyklus (Create, Read) für Kategorien.
     *
     * Test-Ablauf:
     * 1. Erstellt eine Test-Kategorie mit spezifischer Beschreibung
     * 2. Speichert sie in der Datenbank
     * 3. Ruft sie anhand der generierten ID wieder ab
     * 4. Überprüft die Konsistenz und Korrektheit der Daten
     */
    public function testCategoryCreationAndRetrieval(): void
    {
        // ===== ARRANGE Phase =====
        // Neue Kategorie mit spezifischer Beschreibung erstellen
        $category = new Category();
        $category->setDiscription('Apartments zum Kaufen');

        // ===== ACT Phase =====
        // Kategorie in Datenbank speichern (Persist + Flush)
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // Kategorie anhand der automatisch generierten ID aus Datenbank abrufen
        $foundCategory = $this->categoryRepository->find($category->getId());

        // ===== ASSERT Phase =====
        // Test: Die gefundene Kategorie sollte nicht null sein
        $this->assertNotNull(
            $foundCategory,
            'Kategorie sollte in der Datenbank gefunden werden'
        );

        // Test: Die Beschreibung sollte exakt mit dem gespeicherten Wert übereinstimmen
        $this->assertEquals(
            'Apartments zum Kaufen',
            $foundCategory->getDiscription(),
            'Kategorie-Beschreibung sollte mit dem gespeicherten Wert übereinstimmen'
        );

        // Test: Die ID sollte konsistent sein (gleiche Instanz)
        $this->assertEquals(
            $category->getId(),
            $foundCategory->getId(),
            'Kategorie-ID sollte konsistent sein über Datenbank-Operationen hinweg'
        );

        // Test: Es sollte sich um dieselbe Entität handeln
        $this->assertSame(
            $category,
            $foundCategory,
            'Es sollte dieselbe Kategorie-Instanz zurückgegeben werden'
        );
    }

    /**
     * Testet das Abrufen aller Kategorien aus der Datenbank
     *
     * Szenario: System benötigt eine komplette Liste aller verfügbaren Kategorien
     * für Übersichtsseiten, Admin-Funktionen oder Filter-Dropdowns.
     * Stellt sicher, dass die findAll()-Methode korrekt funktioniert.
     *
     * Test-Ablauf:
     * 1. Erstellt mehrere Test-Kategorien mit unterschiedlichen Beschreibungen
     * 2. Ruft alle Kategorien aus der Datenbank ab
     * 3. Überprüft die Vollständigkeit, Korrektheit und Typensicherheit der Daten
     */
    public function testFindAllCategories(): void
    {
        // ===== ARRANGE Phase =====
        // Erste Test-Kategorie für Apartments zum Kaufen
        $category1 = new Category();
        $category1->setDiscription('Apartments zum Kaufen');

        // Zweite Test-Kategorie für Häuser zum Mieten
        $category2 = new Category();
        $category2->setDiscription('Häuser zum Mieten');

        // Dritte Test-Kategorie für Gewerbeimmobilien
        $category3 = new Category();
        $category3->setDiscription('Gewerbeimmobilien');

        // Test-Daten in Datenbank speichern
        $this->entityManager->persist($category1);
        $this->entityManager->persist($category2);
        $this->entityManager->persist($category3);
        $this->entityManager->flush();

        // ===== ACT Phase =====
        // Alle Kategorien aus Datenbank abrufen
        $allCategories = $this->categoryRepository->findAll();

        // ===== ASSERT Phase =====
        // Test: Rückgabe sollte ein Array sein
        $this->assertIsArray(
            $allCategories,
            'Rückgabe der findAll()-Methode sollte ein Array sein'
        );

        // Test: Mindestens 3 Kategorien sollten zurückgegeben werden
        $this->assertGreaterThanOrEqual(
            3,
            count($allCategories),
            'Es sollten mindestens 3 Kategorien zurückgegeben werden'
        );

        // Test: Alle Elemente sollten Category-Instanzen sein (Typensicherheit)
        $this->assertContainsOnlyInstancesOf(
            Category::class,
            $allCategories,
            'Alle Elemente sollten Category-Instanzen sein'
        );

        // Test: Unsere Test-Kategorien sollten in der Liste enthalten sein
        $categoryDescriptions = array_map(
            fn($cat) => $cat->getDiscription(),
            $allCategories
        );

        $this->assertContains(
            'Apartments zum Kaufen',
            $categoryDescriptions,
            'Test-Kategorie "Apartments zum Kaufen" sollte in der Liste enthalten sein'
        );

        $this->assertContains(
            'Häuser zum Mieten',
            $categoryDescriptions,
            'Test-Kategorie "Häuser zum Mieten" sollte in der Liste enthalten sein'
        );
    }

    /**
     * Testet das Finden einer Kategorie anhand ihrer Beschreibung
     *
     * Szenario: System muss eine spezifische Kategorie anhand ihres Namens
     * finden können, z.B. für Slug-basierte URLs oder spezifische Filter.
     *
     * Test-Ablauf:
     * 1. Erstellt eine Test-Kategorie mit eindeutiger Beschreibung
     * 2 Sucht die Kategorie anhand der Beschreibung
     * 3. Überprüft ob die korrekte Kategorie gefunden wird
     */
    public function testFindCategoryByDescription(): void
    {
        // ===== ARRANGE Phase =====
        // Test-Kategorie mit eindeutiger Beschreibung erstellen
        $uniqueDescription = 'Luxus-Villen ' . uniqid();
        $category = new Category();
        $category->setDiscription($uniqueDescription);

        // Test-Daten in Datenbank speichern
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // ===== ACT Phase =====
        // Kategorie anhand der Beschreibung finden
        $foundCategories = $this->categoryRepository->findBy([
            'discription' => $uniqueDescription
        ]);

        // ===== ASSERT Phase =====
        // Test: Es sollte genau eine Kategorie gefunden werden
        $this->assertCount(
            1,
            $foundCategories,
            'Es sollte genau eine Kategorie mit der spezifischen Beschreibung gefunden werden'
        );

        // Test: Die Beschreibung der gefundenen Kategorie sollte korrekt sein
        $this->assertEquals(
            $uniqueDescription,
            $foundCategories[0]->getDiscription(),
            'Die Beschreibung der gefundenen Kategorie sollte korrekt sein'
        );

        // Test: Es sollte dieselbe Kategorie-Instanz sein
        $this->assertEquals(
            $category->getId(),
            $foundCategories[0]->getId(),
            'Es sollte dieselbe Kategorie-Instanz zurückgegeben werden'
        );
    }

    /**
     * Cleanup-Methode - Wird nach jedem Test ausgeführt
     *
     * Stellt den ursprünglichen Zustand wieder her:
     * - Schließt den EntityManager um Datenbankverbindungen freizugeben
     * - Gibt Ressourcen frei für Garbage Collection
     * - Verhindert Memory-Leaks zwischen Tests
     * - Gewährleistet saubere Test-Isolation
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // EntityManager schließen um Datenbankverbindung freizugeben
        $this->entityManager->close();

        // Referenzen auf null setzen für Garbage Collection
        $this->entityManager = null;
        $this->categoryRepository = null;
    }
}