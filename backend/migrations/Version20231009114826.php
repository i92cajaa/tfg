<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231009114826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD report_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8444BD2A4C0 FOREIGN KEY (report_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE38F8444BD2A4C0 ON appointment (report_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8444BD2A4C0');
        $this->addSql('DROP INDEX UNIQ_FE38F8444BD2A4C0 ON appointment');
        $this->addSql('ALTER TABLE appointment DROP report_id');
    }
}
