<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240421101351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lesson ADD duration DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE schedule RENAME INDEX fk_5a3811fb41807e1d TO IDX_5A3811FB41807E1D');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lesson DROP duration');
        $this->addSql('ALTER TABLE schedule RENAME INDEX idx_5a3811fb41807e1d TO FK_5A3811FB41807E1D');
    }
}
