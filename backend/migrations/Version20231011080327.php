<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231011080327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD document_adhesion_id VARCHAR(255) DEFAULT NULL, ADD document_confidencial_id VARCHAR(255) DEFAULT NULL, ADD goals VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045574280EBD FOREIGN KEY (document_adhesion_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404559D687121 FOREIGN KEY (document_confidencial_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C744045574280EBD ON client (document_adhesion_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404559D687121 ON client (document_confidencial_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C744045574280EBD');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404559D687121');
        $this->addSql('DROP INDEX UNIQ_C744045574280EBD ON client');
        $this->addSql('DROP INDEX UNIQ_C74404559D687121 ON client');
        $this->addSql('ALTER TABLE client DROP document_adhesion_id, DROP document_confidencial_id, DROP goals');
    }
}
