<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230920150335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404555932F377');
        $this->addSql('DROP INDEX UNIQ_C74404555932F377 ON client');
        $this->addSql('ALTER TABLE client CHANGE center_id centro_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455298137A7 FOREIGN KEY (centro_id) REFERENCES center (id)');
        $this->addSql('CREATE INDEX IDX_C7440455298137A7 ON client (centro_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455298137A7');
        $this->addSql('DROP INDEX IDX_C7440455298137A7 ON client');
        $this->addSql('ALTER TABLE client CHANGE centro_id center_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404555932F377 FOREIGN KEY (center_id) REFERENCES center (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404555932F377 ON client (center_id)');
    }
}
