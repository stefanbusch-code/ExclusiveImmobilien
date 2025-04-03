<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402065421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location ADD location_id INT AUTO_INCREMENT NOT NULL, DROP id, ADD PRIMARY KEY (location_id)');
        $this->addSql('ALTER TABLE property CHANGE location_id location_location_id INT NOT NULL');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE1098C6CA FOREIGN KEY (location_location_id) REFERENCES location (location_id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDE1098C6CA ON property (location_location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location MODIFY location_id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON location');
        $this->addSql('ALTER TABLE location ADD id INT NOT NULL, DROP location_id');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE1098C6CA');
        $this->addSql('DROP INDEX IDX_8BF21CDE1098C6CA ON property');
        $this->addSql('ALTER TABLE property CHANGE location_location_id location_id INT NOT NULL');
    }
}
