<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429063433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wishlist (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, property_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9CE12A319395C3F3 (customer_id), INDEX IDX_9CE12A31549213EC (property_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A319395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A31549213EC FOREIGN KEY (property_id) REFERENCES property (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wishlist DROP FOREIGN KEY FK_9CE12A319395C3F3');
        $this->addSql('ALTER TABLE wishlist DROP FOREIGN KEY FK_9CE12A31549213EC');
        $this->addSql('DROP TABLE wishlist');
    }
}
