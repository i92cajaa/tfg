<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230926061350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD areas_id VARCHAR(255) DEFAULT NULL, ADD center_id VARCHAR(255) DEFAULT NULL, DROP areas, DROP center');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6491E756D0A FOREIGN KEY (areas_id) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495932F377 FOREIGN KEY (center_id) REFERENCES center (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6491E756D0A ON user (areas_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6495932F377 ON user (center_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6491E756D0A');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495932F377');
        $this->addSql('DROP INDEX UNIQ_8D93D6491E756D0A ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D6495932F377 ON user');
        $this->addSql('ALTER TABLE user ADD areas VARCHAR(180) DEFAULT NULL, ADD center VARCHAR(180) DEFAULT NULL, DROP areas_id, DROP center_id');
    }
}
