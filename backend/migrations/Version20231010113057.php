<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010113057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment ADD report_mentor_id VARCHAR(255) DEFAULT NULL, ADD report_project_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844F5F45D5C FOREIGN KEY (report_mentor_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84452F33A7 FOREIGN KEY (report_project_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE38F844F5F45D5C ON appointment (report_mentor_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FE38F84452F33A7 ON appointment (report_project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844F5F45D5C');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F84452F33A7');
        $this->addSql('DROP INDEX UNIQ_FE38F844F5F45D5C ON appointment');
        $this->addSql('DROP INDEX UNIQ_FE38F84452F33A7 ON appointment');
        $this->addSql('ALTER TABLE appointment DROP report_mentor_id, DROP report_project_id');
    }
}
