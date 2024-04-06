<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240210225505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD mentor_survey_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844716232E5 FOREIGN KEY (mentor_survey_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE38F844716232E5 ON appointment (mentor_survey_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844716232E5');
        $this->addSql('DROP INDEX UNIQ_FE38F844716232E5 ON appointment');
        $this->addSql('ALTER TABLE appointment DROP mentor_survey_id');
    }
}
