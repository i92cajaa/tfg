<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231127100115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD social_name VARCHAR(255) DEFAULT NULL, ADD girl_members VARCHAR(255) DEFAULT NULL, ADD province VARCHAR(255) DEFAULT NULL, ADD cif VARCHAR(255) DEFAULT NULL, ADD incorporation_year VARCHAR(255) DEFAULT NULL, ADD support_type VARCHAR(255) DEFAULT NULL, ADD comment VARCHAR(255) DEFAULT NULL, ADD new_company TINYINT(1) DEFAULT NULL, ADD digital_startup TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP social_name, DROP girl_members, DROP province, DROP cif, DROP incorporation_year, DROP support_type, DROP comment, DROP new_company, DROP digital_startup');
    }
}
