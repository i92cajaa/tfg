<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230914092012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE center ADD logo_id VARCHAR(255) DEFAULT NULL, DROP logo');
        $this->addSql('ALTER TABLE center ADD CONSTRAINT FK_40F0EB24F98F144A FOREIGN KEY (logo_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40F0EB24F98F144A ON center (logo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE center DROP FOREIGN KEY FK_40F0EB24F98F144A');
        $this->addSql('DROP INDEX UNIQ_40F0EB24F98F144A ON center');
        $this->addSql('ALTER TABLE center ADD logo VARCHAR(10) DEFAULT NULL, DROP logo_id');
    }
}
