<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326124351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON property');
        $this->addSql('ALTER TABLE property CHANGE id property_id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE property ADD PRIMARY KEY (property_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property MODIFY property_id INT NOT NULL');
        $this->addSql('DROP INDEX `PRIMARY` ON property');
        $this->addSql('ALTER TABLE property CHANGE property_id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE property ADD PRIMARY KEY (id)');
    }
}
