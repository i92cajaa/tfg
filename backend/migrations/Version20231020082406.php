<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231020082406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_has_document (user_id VARCHAR(255) NOT NULL, document_id VARCHAR(255) NOT NULL, INDEX IDX_49C30C40A76ED395 (user_id), INDEX IDX_49C30C40C33F7837 (document_id), PRIMARY KEY(user_id, document_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_has_document ADD CONSTRAINT FK_49C30C40A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_document ADD CONSTRAINT FK_49C30C40C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD document_adhesion_id VARCHAR(255) DEFAULT NULL, ADD document_confidencial_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64974280EBD FOREIGN KEY (document_adhesion_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499D687121 FOREIGN KEY (document_confidencial_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64974280EBD ON user (document_adhesion_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6499D687121 ON user (document_confidencial_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_has_document DROP FOREIGN KEY FK_49C30C40A76ED395');
        $this->addSql('ALTER TABLE user_has_document DROP FOREIGN KEY FK_49C30C40C33F7837');
        $this->addSql('DROP TABLE user_has_document');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64974280EBD');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499D687121');
        $this->addSql('DROP INDEX UNIQ_8D93D64974280EBD ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D6499D687121 ON user');
        $this->addSql('ALTER TABLE user DROP document_adhesion_id, DROP document_confidencial_id');
    }
}
