<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230921073635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment CHANGE time_to time_to DATETIME DEFAULT NULL, CHANGE time_from time_from DATETIME DEFAULT NULL, CHANGE email_sent email_sent TINYINT(1) DEFAULT 0, CHANGE total_price total_price DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment CHANGE time_to time_to DATETIME NOT NULL, CHANGE time_from time_from DATETIME NOT NULL, CHANGE email_sent email_sent TINYINT(1) DEFAULT 0 NOT NULL, CHANGE total_price total_price DOUBLE PRECISION NOT NULL');
    }
}
