<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230926100014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_has_area (user_id VARCHAR(255) NOT NULL, area_id VARCHAR(255) NOT NULL, INDEX IDX_6A450237A76ED395 (user_id), INDEX IDX_6A450237BD0F409C (area_id), UNIQUE INDEX area_unique (user_id, area_id), PRIMARY KEY(user_id, area_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_has_area ADD CONSTRAINT FK_6A450237A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_area ADD CONSTRAINT FK_6A450237BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491E756D0A');
        $this->addSql('DROP INDEX UNIQ_8D93D6491E756D0A ON user');
        $this->addSql('ALTER TABLE user DROP areas_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_has_area DROP FOREIGN KEY FK_6A450237A76ED395');
        $this->addSql('ALTER TABLE user_has_area DROP FOREIGN KEY FK_6A450237BD0F409C');
        $this->addSql('DROP TABLE user_has_area');
        $this->addSql('ALTER TABLE user ADD areas_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491E756D0A FOREIGN KEY (areas_id) REFERENCES area (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6491E756D0A ON user (areas_id)');
    }
}
