<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409112444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE area (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking (client_id VARCHAR(255) NOT NULL, schedule_id VARCHAR(255) NOT NULL, INDEX IDX_E00CEDDE19EB6921 (client_id), INDEX IDX_E00CEDDEA40BC2D5 (schedule_id), PRIMARY KEY(client_id, schedule_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE center (id VARCHAR(255) NOT NULL, logo_id VARCHAR(255) DEFAULT NULL, area_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_40F0EB24F98F144A (logo_id), INDEX IDX_40F0EB24BD0F409C (area_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, surnames VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, dni VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, status TINYINT(1) NOT NULL, temporal_hash VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C7440455E7927C74 (email), UNIQUE INDEX UNIQ_C74404557F8F253B (dni), UNIQUE INDEX UNIQ_C7440455DD26A079 (temporal_hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_has_document (client_id VARCHAR(255) NOT NULL, document_id VARCHAR(255) NOT NULL, INDEX IDX_7805D24319EB6921 (client_id), INDEX IDX_7805D243C33F7837 (document_id), PRIMARY KEY(client_id, document_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE config (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, value LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE config_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, default_value VARCHAR(255) DEFAULT NULL, module TINYINT(1) DEFAULT 0 NOT NULL, module_dependant VARCHAR(255) DEFAULT NULL, order_number INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, extension VARCHAR(10) NOT NULL, mime_type VARCHAR(50) DEFAULT NULL, file_name VARCHAR(255) NOT NULL, subdirectory VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, status TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson (id VARCHAR(255) NOT NULL, image_id VARCHAR(255) DEFAULT NULL, center_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, status TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_F87474F33DA5256D (image_id), INDEX IDX_F87474F35932F377 (center_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, link LONGTEXT DEFAULT NULL, seen TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CA19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, label VARCHAR(180) NOT NULL, action VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, admin_managed TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_E04992AAFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, label VARCHAR(180) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, color VARCHAR(180) NOT NULL, `admin` TINYINT(1) DEFAULT NULL, description VARCHAR(180) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_has_permission (role_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_6F82580FD60322AC (role_id), INDEX IDX_6F82580FFED90CCA (permission_id), UNIQUE INDEX role_permission_unique (role_id, permission_id), PRIMARY KEY(role_id, permission_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id VARCHAR(255) NOT NULL, center_id VARCHAR(255) NOT NULL, floor INT NOT NULL, number INT NOT NULL, capacity INT NOT NULL, INDEX IDX_729F519B5932F377 (center_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE schedule (id VARCHAR(255) NOT NULL, lesson_id VARCHAR(255) NOT NULL, status_id INT NOT NULL, room_id VARCHAR(255) NOT NULL, date_from DATETIME NOT NULL, date_to DATETIME NOT NULL, INDEX IDX_5A3811FBCDF80196 (lesson_id), INDEX IDX_5A3811FB6BF700BD (status_id), INDEX IDX_5A3811FB54177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, color VARCHAR(255) NOT NULL, entity_type VARCHAR(30) NOT NULL, status_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, img_profile_id VARCHAR(255) DEFAULT NULL, center_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, surnames VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, status TINYINT(1) NOT NULL, locale VARCHAR(255) NOT NULL, calendar_interval VARCHAR(255) DEFAULT NULL, temporal_hash VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D64922136525 (img_profile_id), INDEX IDX_8D93D6495932F377 (center_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_has_document (user_id VARCHAR(255) NOT NULL, document_id VARCHAR(255) NOT NULL, INDEX IDX_49C30C40A76ED395 (user_id), INDEX IDX_49C30C40C33F7837 (document_id), PRIMARY KEY(user_id, document_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_has_lesson (user_id VARCHAR(255) NOT NULL, lesson_id VARCHAR(255) NOT NULL, INDEX IDX_7331A584A76ED395 (user_id), INDEX IDX_7331A584CDF80196 (lesson_id), PRIMARY KEY(user_id, lesson_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_has_permission (user_id VARCHAR(255) NOT NULL, permission_id INT NOT NULL, INDEX IDX_6D8EB460A76ED395 (user_id), INDEX IDX_6D8EB460FED90CCA (permission_id), UNIQUE INDEX permission_unique (user_id, permission_id), PRIMARY KEY(user_id, permission_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_has_role (user_id VARCHAR(255) NOT NULL, role_id INT NOT NULL, INDEX IDX_EAB8B535A76ED395 (user_id), INDEX IDX_EAB8B535D60322AC (role_id), UNIQUE INDEX role_unique (user_id, role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE center ADD CONSTRAINT FK_40F0EB24F98F144A FOREIGN KEY (logo_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE center ADD CONSTRAINT FK_40F0EB24BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_has_document ADD CONSTRAINT FK_7805D24319EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_has_document ADD CONSTRAINT FK_7805D243C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F33DA5256D FOREIGN KEY (image_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F35932F377 FOREIGN KEY (center_id) REFERENCES center (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AAFE54D947 FOREIGN KEY (group_id) REFERENCES permission_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_has_permission ADD CONSTRAINT FK_6F82580FD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_has_permission ADD CONSTRAINT FK_6F82580FFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B5932F377 FOREIGN KEY (center_id) REFERENCES center (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB6BF700BD FOREIGN KEY (status_id) REFERENCES status (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB54177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64922136525 FOREIGN KEY (img_profile_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495932F377 FOREIGN KEY (center_id) REFERENCES center (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_has_document ADD CONSTRAINT FK_49C30C40A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_document ADD CONSTRAINT FK_49C30C40C33F7837 FOREIGN KEY (document_id) REFERENCES document (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_lesson ADD CONSTRAINT FK_7331A584A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_lesson ADD CONSTRAINT FK_7331A584CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_permission ADD CONSTRAINT FK_6D8EB460A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_permission ADD CONSTRAINT FK_6D8EB460FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_role ADD CONSTRAINT FK_EAB8B535A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_role ADD CONSTRAINT FK_EAB8B535D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE19EB6921');
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA40BC2D5');
        $this->addSql('ALTER TABLE center DROP FOREIGN KEY FK_40F0EB24F98F144A');
        $this->addSql('ALTER TABLE center DROP FOREIGN KEY FK_40F0EB24BD0F409C');
        $this->addSql('ALTER TABLE client_has_document DROP FOREIGN KEY FK_7805D24319EB6921');
        $this->addSql('ALTER TABLE client_has_document DROP FOREIGN KEY FK_7805D243C33F7837');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F33DA5256D');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F35932F377');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA19EB6921');
        $this->addSql('ALTER TABLE permission DROP FOREIGN KEY FK_E04992AAFE54D947');
        $this->addSql('ALTER TABLE role_has_permission DROP FOREIGN KEY FK_6F82580FD60322AC');
        $this->addSql('ALTER TABLE role_has_permission DROP FOREIGN KEY FK_6F82580FFED90CCA');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B5932F377');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBCDF80196');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB6BF700BD');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FB54177093');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64922136525');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495932F377');
        $this->addSql('ALTER TABLE user_has_document DROP FOREIGN KEY FK_49C30C40A76ED395');
        $this->addSql('ALTER TABLE user_has_document DROP FOREIGN KEY FK_49C30C40C33F7837');
        $this->addSql('ALTER TABLE user_has_lesson DROP FOREIGN KEY FK_7331A584A76ED395');
        $this->addSql('ALTER TABLE user_has_lesson DROP FOREIGN KEY FK_7331A584CDF80196');
        $this->addSql('ALTER TABLE user_has_permission DROP FOREIGN KEY FK_6D8EB460A76ED395');
        $this->addSql('ALTER TABLE user_has_permission DROP FOREIGN KEY FK_6D8EB460FED90CCA');
        $this->addSql('ALTER TABLE user_has_role DROP FOREIGN KEY FK_EAB8B535A76ED395');
        $this->addSql('ALTER TABLE user_has_role DROP FOREIGN KEY FK_EAB8B535D60322AC');
        $this->addSql('DROP TABLE area');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE center');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_has_document');
        $this->addSql('DROP TABLE config');
        $this->addSql('DROP TABLE config_type');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE permission_group');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_has_permission');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_has_document');
        $this->addSql('DROP TABLE user_has_lesson');
        $this->addSql('DROP TABLE user_has_permission');
        $this->addSql('DROP TABLE user_has_role');
    }
}
