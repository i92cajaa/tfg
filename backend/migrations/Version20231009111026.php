<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231009111026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD area_id VARCHAR(255) DEFAULT NULL, ADD modality VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844BD0F409C FOREIGN KEY (area_id) REFERENCES area (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE38F844BD0F409C ON appointment (area_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844BD0F409C');
        $this->addSql('DROP INDEX UNIQ_FE38F844BD0F409C ON appointment');
        $this->addSql('ALTER TABLE appointment DROP area_id, DROP modality');
    }
}
