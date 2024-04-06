<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230926085437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP nif, DROP address, DROP autonomous, DROP appointment_percentage, DROP hour_price');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD nif VARCHAR(180) DEFAULT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD autonomous TINYINT(1) DEFAULT 0 NOT NULL, ADD appointment_percentage DOUBLE PRECISION DEFAULT NULL, ADD hour_price DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6491E756D0A ON user (areas_id)');
    }
}
