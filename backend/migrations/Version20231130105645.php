<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231130105645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment_client (appointment_id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, INDEX IDX_801ABB1BE5B533F9 (appointment_id), INDEX IDX_801ABB1B19EB6921 (client_id), PRIMARY KEY(appointment_id, client_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointment_client ADD CONSTRAINT FK_801ABB1BE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment_client ADD CONSTRAINT FK_801ABB1B19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F84419EB6921');
        $this->addSql('DROP INDEX IDX_FE38F84419EB6921 ON appointment');
        $this->addSql('ALTER TABLE appointment DROP client_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment_client DROP FOREIGN KEY FK_801ABB1BE5B533F9');
        $this->addSql('ALTER TABLE appointment_client DROP FOREIGN KEY FK_801ABB1B19EB6921');
        $this->addSql('DROP TABLE appointment_client');
        $this->addSql('ALTER TABLE appointment ADD client_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_FE38F84419EB6921 ON appointment (client_id)');
    }
}
