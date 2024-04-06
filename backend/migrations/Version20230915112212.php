<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230915112212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C744045522136525');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C744045525142B0D');
        $this->addSql('DROP INDEX UNIQ_C744045522136525 ON client');
        $this->addSql('DROP INDEX IDX_C744045525142B0D ON client');
        $this->addSql('ALTER TABLE client ADD logo_id VARCHAR(255) DEFAULT NULL, ADD documents_id VARCHAR(255) DEFAULT NULL, ADD members VARCHAR(255) NOT NULL, ADD speciality VARCHAR(255) NOT NULL, ADD description VARCHAR(255) NOT NULL, ADD phone VARCHAR(255) NOT NULL, ADD email2 VARCHAR(255) NOT NULL, DROP payment_preference_id, DROP img_profile_id, DROP surnames, DROP dni, DROP phone1, DROP address, DROP extra_person, DROP menu_expanded, DROP comments, DROP locale, DROP timezone, DROP first_paid, DROP available_time_slots, DROP calendar_interval, CHANGE phone2 phone2 VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE password representative VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F98F144A FOREIGN KEY (logo_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404555F0F2752 FOREIGN KEY (documents_id) REFERENCES document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455F98F144A ON client (logo_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404555F0F2752 ON client (documents_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F98F144A');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404555F0F2752');
        $this->addSql('DROP INDEX UNIQ_C7440455F98F144A ON client');
        $this->addSql('DROP INDEX UNIQ_C74404555F0F2752 ON client');
        $this->addSql('ALTER TABLE client ADD payment_preference_id INT DEFAULT NULL, ADD img_profile_id VARCHAR(255) DEFAULT NULL, ADD surnames VARCHAR(255) DEFAULT NULL, ADD password VARCHAR(255) NOT NULL, ADD dni VARCHAR(255) DEFAULT NULL, ADD phone1 VARCHAR(255) DEFAULT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD extra_person VARCHAR(255) DEFAULT NULL, ADD menu_expanded TINYINT(1) DEFAULT 1 NOT NULL, ADD comments LONGTEXT DEFAULT NULL, ADD locale LONGTEXT DEFAULT NULL, ADD timezone LONGTEXT DEFAULT NULL, ADD first_paid TINYINT(1) DEFAULT 0 NOT NULL, ADD available_time_slots LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD calendar_interval VARCHAR(255) DEFAULT NULL, DROP logo_id, DROP documents_id, DROP representative, DROP members, DROP speciality, DROP description, DROP phone, DROP email2, CHANGE phone2 phone2 VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045522136525 FOREIGN KEY (img_profile_id) REFERENCES document (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045525142B0D FOREIGN KEY (payment_preference_id) REFERENCES payment_method (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C744045522136525 ON client (img_profile_id)');
        $this->addSql('CREATE INDEX IDX_C744045525142B0D ON client (payment_preference_id)');
    }
}
