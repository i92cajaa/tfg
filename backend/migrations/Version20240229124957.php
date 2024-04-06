<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229124957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE survey_range (id VARCHAR(255) NOT NULL, start_date DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, is_startup_survey TINYINT(1) DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD survey_range_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A7676759696 FOREIGN KEY (survey_range_id) REFERENCES survey_range (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D8698A7676759696 ON document (survey_range_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A7676759696');
        $this->addSql('DROP TABLE survey_range');
        $this->addSql('DROP INDEX UNIQ_D8698A7676759696 ON document');
        $this->addSql('ALTER TABLE document DROP survey_range_id');
    }
}
