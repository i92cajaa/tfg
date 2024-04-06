<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230920070200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_has_document (client_id VARCHAR(255) NOT NULL, document_id VARCHAR(255) NOT NULL, INDEX IDX_7805D24319EB6921 (client_id), INDEX IDX_7805D243C33F7837 (document_id), PRIMARY KEY(client_id, document_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_has_document ADD CONSTRAINT FK_7805D24319EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_has_document ADD CONSTRAINT FK_7805D243C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404555F0F2752');
        $this->addSql('DROP INDEX UNIQ_C74404555F0F2752 ON client');
        $this->addSql('ALTER TABLE client ADD representative2 VARCHAR(255) NOT NULL, ADD position2 VARCHAR(255) DEFAULT NULL, ADD representative3 VARCHAR(255) NOT NULL, ADD position3 VARCHAR(255) DEFAULT NULL, ADD phone3 VARCHAR(255) DEFAULT NULL, ADD email3 VARCHAR(255) NOT NULL, CHANGE documents_id position VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client_has_document DROP FOREIGN KEY FK_7805D24319EB6921');
        $this->addSql('ALTER TABLE client_has_document DROP FOREIGN KEY FK_7805D243C33F7837');
        $this->addSql('DROP TABLE client_has_document');
        $this->addSql('ALTER TABLE client ADD documents_id VARCHAR(255) DEFAULT NULL, DROP position, DROP representative2, DROP position2, DROP representative3, DROP position3, DROP phone3, DROP email3');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404555F0F2752 FOREIGN KEY (documents_id) REFERENCES document (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404555F0F2752 ON client (documents_id)');
    }
}
