<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230913053654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, schedules_id VARCHAR(255) NOT NULL, status_id INT NOT NULL, meeting_id VARCHAR(255) DEFAULT NULL, status TINYINT(1) NOT NULL, time_to DATETIME NOT NULL, time_from DATETIME NOT NULL, email_sent TINYINT(1) DEFAULT 0 NOT NULL, total_price DOUBLE PRECISION NOT NULL, stripe_id VARCHAR(255) DEFAULT NULL, paid TINYINT(1) DEFAULT 0 NOT NULL, periodic_id VARCHAR(255) DEFAULT NULL, meeting_attached TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_FE38F844A76ED395 (user_id), INDEX IDX_FE38F84419EB6921 (client_id), INDEX IDX_FE38F844116C90BC (schedules_id), INDEX IDX_FE38F8446BF700BD (status_id), UNIQUE INDEX UNIQ_FE38F84467433D9C (meeting_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appointment_has_service (appointment_id VARCHAR(255) NOT NULL, service_id VARCHAR(255) NOT NULL, iva DOUBLE PRECISION DEFAULT NULL, iva_applied TINYINT(1) DEFAULT 1 NOT NULL, price DOUBLE PRECISION DEFAULT NULL, INDEX IDX_FDA49095E5B533F9 (appointment_id), INDEX IDX_FDA49095ED5CA9E6 (service_id), PRIMARY KEY(appointment_id, service_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appointment_log (id VARCHAR(255) NOT NULL, appointment_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, job_done VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, comments LONGTEXT DEFAULT NULL, INDEX IDX_206FFFDDE5B533F9 (appointment_id), INDEX IDX_206FFFDDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id VARCHAR(255) NOT NULL, payment_preference_id INT DEFAULT NULL, img_profile_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, surnames VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, dni VARCHAR(255) DEFAULT NULL, phone1 VARCHAR(255) DEFAULT NULL, phone2 VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, extra_person VARCHAR(255) DEFAULT NULL, menu_expanded TINYINT(1) DEFAULT 1 NOT NULL, status TINYINT(1) NOT NULL, comments LONGTEXT DEFAULT NULL, locale LONGTEXT DEFAULT NULL, timezone LONGTEXT DEFAULT NULL, first_paid TINYINT(1) DEFAULT 0 NOT NULL, available_time_slots LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, calendar_interval VARCHAR(255) DEFAULT NULL, INDEX IDX_C744045525142B0D (payment_preference_id), UNIQUE INDEX UNIQ_C744045522136525 (img_profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_request (id VARCHAR(255) NOT NULL, status_id INT NOT NULL, name VARCHAR(255) NOT NULL, surnames VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, comments VARCHAR(255) DEFAULT NULL, available_time_slots LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', locale LONGTEXT DEFAULT NULL, timezone LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, first_answer LONGTEXT DEFAULT NULL, second_answer LONGTEXT DEFAULT NULL, third_answer LONGTEXT DEFAULT NULL, fourth_answer LONGTEXT DEFAULT NULL, fifth_answer LONGTEXT DEFAULT NULL, sixth_answer LONGTEXT DEFAULT NULL, stripe_id VARCHAR(255) DEFAULT NULL, paid TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_69AACBE26BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE config (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, value LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE config_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, default_value VARCHAR(255) DEFAULT NULL, module TINYINT(1) DEFAULT 0 NOT NULL, module_dependant VARCHAR(255) DEFAULT NULL, order_number INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE division (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, extension VARCHAR(10) NOT NULL, mime_type VARCHAR(50) DEFAULT NULL, file_name VARCHAR(255) NOT NULL, subdirectory VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, status TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extra_appointment_field (id VARCHAR(255) NOT NULL, appointment_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, value LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_23A8E35BE5B533F9 (appointment_id), INDEX IDX_23A8E35BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE extra_appointment_field_type (id INT AUTO_INCREMENT NOT NULL, division_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', description VARCHAR(255) DEFAULT NULL, position VARCHAR(255) DEFAULT NULL, INDEX IDX_33304C8741859289 (division_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE festive (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) DEFAULT NULL, date DATE NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_74A72E94A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice (id VARCHAR(255) NOT NULL, appointment_id VARCHAR(255) DEFAULT NULL, user_id VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, amount_with_iva DOUBLE PRECISION NOT NULL, serie VARCHAR(255) NOT NULL, invoice_position INT NOT NULL, invoice_number VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, dni VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, social_reason VARCHAR(255) DEFAULT NULL, billing_address VARCHAR(255) DEFAULT NULL, cif VARCHAR(255) DEFAULT NULL, company_phone VARCHAR(255) DEFAULT NULL, payment_method VARCHAR(255) DEFAULT NULL, entity VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, invoice_date DATETIME DEFAULT NULL, breakdown LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_906517442DA68207 (invoice_number), INDEX IDX_90651744E5B533F9 (appointment_id), INDEX IDX_90651744A76ED395 (user_id), INDEX IDX_9065174419EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting (id VARCHAR(255) NOT NULL, appointment_id VARCHAR(255) DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, join_url LONGTEXT NOT NULL, join_web_url LONGTEXT NOT NULL, meeting_code LONGTEXT DEFAULT NULL, options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_F515E139E5B533F9 (appointment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, link LONGTEXT DEFAULT NULL, seen TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id VARCHAR(255) NOT NULL, client_id VARCHAR(255) DEFAULT NULL, appointment_id VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, payment_method VARCHAR(255) NOT NULL, service VARCHAR(255) NOT NULL, payment_date DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, INDEX IDX_6D28840D19EB6921 (client_id), INDEX IDX_6D28840DE5B533F9 (appointment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_7B61A1F65E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, group_id INT DEFAULT NULL, label VARCHAR(180) NOT NULL, action VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, admin_managed TINYINT(1) DEFAULT 0 NOT NULL, module_dependant VARCHAR(200) DEFAULT NULL, INDEX IDX_E04992AAFE54D947 (group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, label VARCHAR(180) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, color VARCHAR(180) NOT NULL, `admin` TINYINT(1) DEFAULT NULL, description VARCHAR(180) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_has_permission (role_id INT NOT NULL, permission_id INT NOT NULL, INDEX IDX_6F82580FD60322AC (role_id), INDEX IDX_6F82580FFED90CCA (permission_id), UNIQUE INDEX role_permission_unique (role_id, permission_id), PRIMARY KEY(role_id, permission_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE schedules (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, time_from DATETIME NOT NULL, time_to DATETIME NOT NULL, week_day SMALLINT NOT NULL, status TINYINT(1) DEFAULT 1 NOT NULL, fixed TINYINT(1) DEFAULT NULL, INDEX IDX_313BDC8EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id VARCHAR(255) NOT NULL, division_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION NOT NULL, iva DOUBLE PRECISION DEFAULT NULL, needed_time INT DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, iva_applied TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_E19D9AD241859289 (division_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, color VARCHAR(255) NOT NULL, entity_type VARCHAR(30) NOT NULL, status_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, appointment_id VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) DEFAULT NULL, status_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, timestamp INT DEFAULT NULL, creation_date DATETIME NOT NULL, estimated_start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, INDEX IDX_527EDB25A76ED395 (user_id), INDEX IDX_527EDB25E5B533F9 (appointment_id), INDEX IDX_527EDB2519EB6921 (client_id), INDEX IDX_527EDB256BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_has_status (id VARCHAR(255) NOT NULL, task_id VARCHAR(255) DEFAULT NULL, status_id INT DEFAULT NULL, created_at DATE NOT NULL, INDEX IDX_4B3139288DB60186 (task_id), INDEX IDX_4B3139286BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, template_type_id VARCHAR(255) NOT NULL, client_id VARCHAR(255) DEFAULT NULL, appointment_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, INDEX IDX_97601F83A76ED395 (user_id), INDEX IDX_97601F8396F4F7AA (template_type_id), INDEX IDX_97601F8319EB6921 (client_id), INDEX IDX_97601F83E5B533F9 (appointment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template_line (id VARCHAR(255) NOT NULL, template_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, value LONGTEXT DEFAULT NULL, INDEX IDX_4DA9E9B5DA0FB8 (template_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template_line_type (id VARCHAR(255) NOT NULL, template_type_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, options LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_CEC0D24696F4F7AA (template_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template_type (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, entity VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE token (id VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, token_type VARCHAR(255) DEFAULT NULL, expiration_date DATETIME DEFAULT NULL, ext_expiration_date DATETIME DEFAULT NULL, default_value LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, img_profile_id VARCHAR(255) DEFAULT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(180) NOT NULL, surnames VARCHAR(180) NOT NULL, nif VARCHAR(180) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, appointment_color VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, last_login DATETIME DEFAULT NULL, status TINYINT(1) NOT NULL, dark_mode TINYINT(1) NOT NULL, menu_expanded TINYINT(1) DEFAULT 1 NOT NULL, vip TINYINT(1) DEFAULT 0 NOT NULL, autonomous TINYINT(1) DEFAULT 0 NOT NULL, appointment_percentage DOUBLE PRECISION DEFAULT NULL, calendar_interval VARCHAR(255) DEFAULT NULL, task_color VARCHAR(255) DEFAULT NULL, locale VARCHAR(255) DEFAULT NULL, hour_price DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D64922136525 (img_profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_has_client (user_id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, INDEX IDX_4C01D522A76ED395 (user_id), INDEX IDX_4C01D52219EB6921 (client_id), UNIQUE INDEX client_unique (user_id, client_id), PRIMARY KEY(user_id, client_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_has_permission (user_id VARCHAR(255) NOT NULL, permission_id INT NOT NULL, INDEX IDX_6D8EB460A76ED395 (user_id), INDEX IDX_6D8EB460FED90CCA (permission_id), UNIQUE INDEX permission_unique (user_id, permission_id), PRIMARY KEY(user_id, permission_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_has_role (user_id VARCHAR(255) NOT NULL, role_id INT NOT NULL, INDEX IDX_EAB8B535A76ED395 (user_id), INDEX IDX_EAB8B535D60322AC (role_id), UNIQUE INDEX role_unique (user_id, role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_has_service (user_id VARCHAR(255) NOT NULL, service_id VARCHAR(255) NOT NULL, INDEX IDX_2F773B9CA76ED395 (user_id), INDEX IDX_2F773B9CED5CA9E6 (service_id), UNIQUE INDEX service_unique (user_id, service_id), PRIMARY KEY(user_id, service_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844116C90BC FOREIGN KEY (schedules_id) REFERENCES schedules (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8446BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F84467433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment_has_service ADD CONSTRAINT FK_FDA49095E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment_has_service ADD CONSTRAINT FK_FDA49095ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment_log ADD CONSTRAINT FK_206FFFDDE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointment_log ADD CONSTRAINT FK_206FFFDDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045525142B0D FOREIGN KEY (payment_preference_id) REFERENCES payment_method (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045522136525 FOREIGN KEY (img_profile_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE client_request ADD CONSTRAINT FK_69AACBE26BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE extra_appointment_field ADD CONSTRAINT FK_23A8E35BE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE extra_appointment_field ADD CONSTRAINT FK_23A8E35BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE extra_appointment_field_type ADD CONSTRAINT FK_33304C8741859289 FOREIGN KEY (division_id) REFERENCES division (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE festive ADD CONSTRAINT FK_74A72E94A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_9065174419EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DE5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permission ADD CONSTRAINT FK_E04992AAFE54D947 FOREIGN KEY (group_id) REFERENCES permission_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_has_permission ADD CONSTRAINT FK_6F82580FD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_has_permission ADD CONSTRAINT FK_6F82580FFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE schedules ADD CONSTRAINT FK_313BDC8EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD241859289 FOREIGN KEY (division_id) REFERENCES division (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2519EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB256BF700BD FOREIGN KEY (status_id) REFERENCES task_has_status (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_has_status ADD CONSTRAINT FK_4B3139288DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_has_status ADD CONSTRAINT FK_4B3139286BF700BD FOREIGN KEY (status_id) REFERENCES status (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE template ADD CONSTRAINT FK_97601F83A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE template ADD CONSTRAINT FK_97601F8396F4F7AA FOREIGN KEY (template_type_id) REFERENCES template_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE template ADD CONSTRAINT FK_97601F8319EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE template ADD CONSTRAINT FK_97601F83E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE template_line ADD CONSTRAINT FK_4DA9E9B5DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE template_line_type ADD CONSTRAINT FK_CEC0D24696F4F7AA FOREIGN KEY (template_type_id) REFERENCES template_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64922136525 FOREIGN KEY (img_profile_id) REFERENCES document (id)');
        $this->addSql('ALTER TABLE user_has_client ADD CONSTRAINT FK_4C01D522A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_client ADD CONSTRAINT FK_4C01D52219EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_permission ADD CONSTRAINT FK_6D8EB460A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_permission ADD CONSTRAINT FK_6D8EB460FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_role ADD CONSTRAINT FK_EAB8B535A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_role ADD CONSTRAINT FK_EAB8B535D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_service ADD CONSTRAINT FK_2F773B9CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_service ADD CONSTRAINT FK_2F773B9CED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844A76ED395');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F84419EB6921');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844116C90BC');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8446BF700BD');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F84467433D9C');
        $this->addSql('ALTER TABLE appointment_has_service DROP FOREIGN KEY FK_FDA49095E5B533F9');
        $this->addSql('ALTER TABLE appointment_has_service DROP FOREIGN KEY FK_FDA49095ED5CA9E6');
        $this->addSql('ALTER TABLE appointment_log DROP FOREIGN KEY FK_206FFFDDE5B533F9');
        $this->addSql('ALTER TABLE appointment_log DROP FOREIGN KEY FK_206FFFDDA76ED395');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C744045525142B0D');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C744045522136525');
        $this->addSql('ALTER TABLE client_request DROP FOREIGN KEY FK_69AACBE26BF700BD');
        $this->addSql('ALTER TABLE extra_appointment_field DROP FOREIGN KEY FK_23A8E35BE5B533F9');
        $this->addSql('ALTER TABLE extra_appointment_field DROP FOREIGN KEY FK_23A8E35BA76ED395');
        $this->addSql('ALTER TABLE extra_appointment_field_type DROP FOREIGN KEY FK_33304C8741859289');
        $this->addSql('ALTER TABLE festive DROP FOREIGN KEY FK_74A72E94A76ED395');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744E5B533F9');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744A76ED395');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_9065174419EB6921');
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E139E5B533F9');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D19EB6921');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DE5B533F9');
        $this->addSql('ALTER TABLE permission DROP FOREIGN KEY FK_E04992AAFE54D947');
        $this->addSql('ALTER TABLE role_has_permission DROP FOREIGN KEY FK_6F82580FD60322AC');
        $this->addSql('ALTER TABLE role_has_permission DROP FOREIGN KEY FK_6F82580FFED90CCA');
        $this->addSql('ALTER TABLE schedules DROP FOREIGN KEY FK_313BDC8EA76ED395');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD241859289');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25A76ED395');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25E5B533F9');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2519EB6921');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB256BF700BD');
        $this->addSql('ALTER TABLE task_has_status DROP FOREIGN KEY FK_4B3139288DB60186');
        $this->addSql('ALTER TABLE task_has_status DROP FOREIGN KEY FK_4B3139286BF700BD');
        $this->addSql('ALTER TABLE template DROP FOREIGN KEY FK_97601F83A76ED395');
        $this->addSql('ALTER TABLE template DROP FOREIGN KEY FK_97601F8396F4F7AA');
        $this->addSql('ALTER TABLE template DROP FOREIGN KEY FK_97601F8319EB6921');
        $this->addSql('ALTER TABLE template DROP FOREIGN KEY FK_97601F83E5B533F9');
        $this->addSql('ALTER TABLE template_line DROP FOREIGN KEY FK_4DA9E9B5DA0FB8');
        $this->addSql('ALTER TABLE template_line_type DROP FOREIGN KEY FK_CEC0D24696F4F7AA');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64922136525');
        $this->addSql('ALTER TABLE user_has_client DROP FOREIGN KEY FK_4C01D522A76ED395');
        $this->addSql('ALTER TABLE user_has_client DROP FOREIGN KEY FK_4C01D52219EB6921');
        $this->addSql('ALTER TABLE user_has_permission DROP FOREIGN KEY FK_6D8EB460A76ED395');
        $this->addSql('ALTER TABLE user_has_permission DROP FOREIGN KEY FK_6D8EB460FED90CCA');
        $this->addSql('ALTER TABLE user_has_role DROP FOREIGN KEY FK_EAB8B535A76ED395');
        $this->addSql('ALTER TABLE user_has_role DROP FOREIGN KEY FK_EAB8B535D60322AC');
        $this->addSql('ALTER TABLE user_has_service DROP FOREIGN KEY FK_2F773B9CA76ED395');
        $this->addSql('ALTER TABLE user_has_service DROP FOREIGN KEY FK_2F773B9CED5CA9E6');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE appointment_has_service');
        $this->addSql('DROP TABLE appointment_log');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE client_request');
        $this->addSql('DROP TABLE config');
        $this->addSql('DROP TABLE config_type');
        $this->addSql('DROP TABLE division');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE extra_appointment_field');
        $this->addSql('DROP TABLE extra_appointment_field_type');
        $this->addSql('DROP TABLE festive');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE meeting');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE permission_group');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_has_permission');
        $this->addSql('DROP TABLE schedules');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE task_has_status');
        $this->addSql('DROP TABLE template');
        $this->addSql('DROP TABLE template_line');
        $this->addSql('DROP TABLE template_line_type');
        $this->addSql('DROP TABLE template_type');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_has_client');
        $this->addSql('DROP TABLE user_has_permission');
        $this->addSql('DROP TABLE user_has_role');
        $this->addSql('DROP TABLE user_has_service');
    }
}
