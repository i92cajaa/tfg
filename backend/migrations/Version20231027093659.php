<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027093659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD document_image_id VARCHAR(255) DEFAULT NULL, ADD document_deontological_id VARCHAR(255) DEFAULT NULL, ADD document_anexo_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B264D434 FOREIGN KEY (document_image_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64995EF0A24 FOREIGN KEY (document_deontological_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64946F5773D FOREIGN KEY (document_anexo_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649B264D434 ON user (document_image_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64995EF0A24 ON user (document_deontological_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64946F5773D ON user (document_anexo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B264D434');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64995EF0A24');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64946F5773D');
        $this->addSql('DROP INDEX UNIQ_8D93D649B264D434 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D64995EF0A24 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D64946F5773D ON user');
        $this->addSql('ALTER TABLE user DROP document_image_id, DROP document_deontological_id, DROP document_anexo_id');
    }
}
