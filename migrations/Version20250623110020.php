<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623110020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE location ADD id INT AUTO_INCREMENT NOT NULL, ADD region VARCHAR(255) NOT NULL, ADD country VARCHAR(255) NOT NULL, DROP location_id, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE property ADD location_id INT NOT NULL, ADD category_id INT NOT NULL, ADD preis INT DEFAULT NULL, ADD bild VARCHAR(255) DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE property_discription property_discription VARCHAR(255) NOT NULL, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDE64D218E ON property (location_id)');
        $this->addSql('CREATE INDEX IDX_8BF21CDE12469DE2 ON property (category_id)');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A31549213EC FOREIGN KEY (property_id) REFERENCES property (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE property MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE64D218E');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE12469DE2');
        $this->addSql('DROP INDEX IDX_8BF21CDE64D218E ON property');
        $this->addSql('DROP INDEX IDX_8BF21CDE12469DE2 ON property');
        $this->addSql('DROP INDEX `primary` ON property');
        $this->addSql('ALTER TABLE property DROP location_id, DROP category_id, DROP preis, DROP bild, CHANGE id id INT NOT NULL, CHANGE property_discription property_discription VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE location MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON location');
        $this->addSql('ALTER TABLE location ADD location_id INT NOT NULL, DROP id, DROP region, DROP country');
        $this->addSql('ALTER TABLE wishlist DROP FOREIGN KEY FK_9CE12A31549213EC');
    }
}
