<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Appointment\Appointment;
use App\Entity\Config\ConfigType;
use App\Entity\Invoice\Invoice;
use App\Entity\Payment\PaymentMethod;
use App\Entity\Task\Task;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230913054342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('INSERT INTO role(`id`, `name`, `admin`) VALUES (1,"Jefe de Estudios",1)');
        $this->addSql('INSERT INTO role(`id`, `name`, `admin`) VALUES (2,"Director",1)');
        $this->addSql('INSERT INTO role(`id`, `name`, `admin`) VALUES (3,"Mentor",0)');

        $this->addSql('INSERT INTO payment_method(`name`) VALUES ("Bono")');
        $this->addSql('INSERT INTO payment_method(`name`) VALUES ("Efectivo o Tarjeta")');
        $this->addSql('INSERT INTO payment_method(`name`) VALUES ("Domiciliado")');

        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (1, 'Solicitada', '#00cfe8', '" . Appointment::ENTITY . "', 0)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (2, 'Asignada', '#ff9f43', '" . Appointment::ENTITY . "', 1)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (3, 'Confirmada', '#325334', '" . Appointment::ENTITY . "', 2)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (4, 'Pending', '#ff9f43', '" . Task::ENTITY . "', 0)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (5, 'Ongoing', '#7367f0', '" . Task::ENTITY . "', 1)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (6, 'Ended', '#00cfe8', '" . Task::ENTITY . "', 2)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (7, 'Rechazada por dirección', '#800000', '" . Appointment::ENTITY . "', 3)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (8, 'Rechazada por mentor', '#800000', '" . Appointment::ENTITY . "', 4)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (9, 'Terminada', '#7367f0', '" . Appointment::ENTITY . "', 5)");

        $this->addSql("INSERT INTO permission_group (id, name, label) 
                            VALUES 
                            (1, 'users', 'Users'),
                            (2, 'roles', 'Roles'),
                            (3, 'clients', 'Clients'),
                            (4, 'appointments', 'Appointments'),
                            (5, 'festives', 'Absences'),
                            (6, 'payment_methods', 'Payment Methods'),
                            (7, 'services', 'Services'),
                            (8, 'invoices', 'Invoices'),
                            (9, 'configs', 'Configuration'),
                            (10, 'templates', 'Templates'),
                            (11, 'template_types', 'Template Types'),
                            (12, 'extra_appointment_field_types', 'Additional Appointment Field Types'),
                            (13, 'extra_appointment_fields', 'Additional Appointment Fields')
                            
                                                    ");

        $this->addSql("INSERT INTO permission (id, group_id, label, action, admin_managed, module_dependant) 
                            VALUES 
                            (1, 1, 'List', 'list', 0, NULL),
                            (2, 1, 'Show', 'show', 0, NULL),
                            (3, 1, 'Create', 'create', 0, NULL),
                            (4, 1, 'Edit', 'edit', 0, NULL),
                            (5, 1, 'Delete', 'delete', 0, NULL),
                            
                            (6, 2, 'List', 'list', 1, NULL),
                            (7, 2, 'Show', 'show', 1, NULL),
                            (8, 2, 'Create', 'create', 1, NULL),
                            (9, 2, 'Edit', 'edit', 1, NULL),
                            (10, 2, 'Delete', 'delete', 1, NULL),
                            
                            (11, 3, 'List', 'list', 0, NULL),
                            (12, 3, 'Show', 'show', 0, NULL),
                            (13, 3, 'Create', 'create', 0, NULL),
                            (14, 3, 'Edit', 'edit', 0, NULL),
                            (15, 3, 'Delete', 'delete', 0, NULL),
                            
                            (16, 4, 'List', 'list', 0, NULL),
                            (17, 4, 'Show', 'show', 0, NULL),
                            (18, 4, 'Create', 'create', 0, NULL),
                            (19, 4, 'Edit', 'edit', 0, NULL),
                            (20, 4, 'Delete', 'delete', 0, NULL),
                            
                            (21, 5, 'List', 'list', 0, NULL),
                            (22, 5, 'Show', 'show', 0, NULL),
                            (23, 5, 'Create', 'create', 0, NULL),
                            (24, 5, 'Edit', 'edit', 0, NULL),
                            (25, 5, 'Delete', 'delete', 0, NULL),
                            
                            (26, 6, 'List', 'list', 1, '" . PaymentMethod::ENTITY . "'),
                            (27, 6, 'Show', 'show', 1, '" . PaymentMethod::ENTITY . "'),
                            (28, 6, 'Create', 'create', 1, '" . PaymentMethod::ENTITY . "'),
                            (29, 6, 'Edit', 'edit', 1, '" . PaymentMethod::ENTITY . "'),
                            (30, 6, 'Delete', 'delete', 1, '" . PaymentMethod::ENTITY . "'),
                            
                            (31, 7, 'List', 'list', 0, NULL),
                            (32, 7, 'Show', 'show', 0, NULL),
                            (33, 7, 'Create', 'create', 0, NULL),
                            (34, 7, 'Edit', 'edit', 0, NULL),
                            (35, 7, 'Delete', 'delete', 0, NULL),
                            
                            (36, 8, 'List', 'list', 0, '" . Invoice::ENTITY . "'),
                            (37, 8, 'Show', 'show', 0, '" . Invoice::ENTITY . "'),
                            (38, 8, 'Create', 'create', 0, '" . Invoice::ENTITY . "'),
                            (39, 8, 'Edit', 'edit', 0, '" . Invoice::ENTITY . "'),
                            (40, 8, 'Delete', 'delete', 0, '" . Invoice::ENTITY . "'),
                            
                            (41, 9, 'Edit', 'edit', 1, NULL),
                            
                            (42, 7, 'Manage Divisions', 'manage_divisions', 0, NULL),
                            (43, 1, 'Manage Services', 'manage_services', 0, NULL),
                            (44, 1, 'Manage Schedules', 'manage_schedules', 0, NULL),
                            
                            (45, 10, 'List', 'list', 0, NULL),
                            (46, 10, 'Create', 'create', 0, NULL),
                            (47, 10, 'Edit', 'edit', 0, NULL),
                            (48, 10, 'Delete', 'delete', 0, NULL),
                            
                            (49, 11, 'List', 'list', 1, NULL),
                            (50, 11, 'Create', 'create', 1, NULL),
                            (51, 11, 'Edit', 'edit', 1, NULL),
                            (52, 11, 'Delete', 'delete', 1, NULL),
                            
                            (53, 10, 'Export', 'export', 0, NULL),
                            
                            (54, 12, 'List', 'list', 1, NULL),
                            (55, 12, 'Create', 'create', 1, NULL),
                            (56, 12, 'Edit', 'edit', 1, NULL),
                            (57, 12, 'Delete', 'delete', 1, NULL),
                            
                            (58, 13, 'List', 'list', 0, NULL),
                            (59, 13, 'Create', 'create', 0, NULL),
                            (60, 13, 'Edit', 'edit', 0, NULL),
                            (61, 13, 'Delete', 'delete', 0, NULL),
                            (62, 13, 'Export', 'export', 0, NULL),
                            
                            (63, 4, 'Manage Payments', 'manage_payments', 0, '" . PaymentMethod::ENTITY . "'),
                            
                            (64, 1, 'Assign Tasks', 'assign_tasks', 0, NULL),
                            
                            (65, 4, 'Modify Hour', 'modify_hour', 1, NULL)
                                                    ");

        $this->addSql("INSERT INTO role_has_permission (role_id, permission_id)
                            VALUES 
                            (1,1),
                            (1,2),
                            (1,3),
                            (1,4),
                            (1,5),
                            (1,6),
                            (1,7),
                            (1,8),
                            (1,9),
                            (1,10),
                            (1,11),
                            (1,12),
                            (1,13),
                            (1,14),
                            (1,15),
                            (1,16),
                            (1,17),
                            (1,18),
                            (1,19),
                            (1,20),
                            (1,21),
                            (1,22),
                            (1,23),
                            (1,24),
                            (1,25),
                            (1,26),
                            (1,27),
                            (1,28),
                            (1,29),
                            (1,30),
                            (1,31),
                            (1,32),
                            (1,33),
                            (1,34),
                            (1,35),
                            (1,36),
                            (1,37),
                            (1,38),
                            (1,39),
                            (1,40),
                            (1,41),
                            (1, 42),
                            (1, 43),
                            (1, 44),
                            (1,45),
                            (1,46),
                            (1,47),
                            (1,48),
                            (1,49),
                            (1,50),
                            (1,51),
                            (1,52),
                            (1,53),
                            (1,54),
                            (1,55),
                            (1,56),
                            (1,57),
                            (1,58),
                            (1,59),
                            (1,60),
                            (1,61),
                            (1,62),
                            (1,63),
                            (1,64),
                            (1,65),
                            
                            (2,1),
                            (2,2),
                            (2,3),
                            (2,4),
                            (2,5),
                            (2,6),
                            (2,7),
                            (2,8),
                            (2,9),
                            (2,10),
                            (2,11),
                            (2,12),
                            (2,13),
                            (2,14),
                            (2,15),
                            (2,16),
                            (2,17),
                            (2,18),
                            (2,19),
                            (2,20),
                            (2,21),
                            (2,22),
                            (2,23),
                            (2,24),
                            (2,25),
                            (2,26),
                            (2,27),
                            (2,28),
                            (2,29),
                            (2,30),
                            (2,31),
                            (2,32),
                            (2,33),
                            (2,34),
                            (2,35),
                            (2,36),
                            (2,37),
                            (2,38),
                            (2,39),
                            (2,40),
                            (2, 42),
                            (2, 43),
                            (2, 44),
                            (2,45),
                            (2,46),
                            (2,47),
                            (2,48),
                            (2,53),
                            (2,58),
                            (2,59),
                            (2,60),
                            (2,61),
                            (2,62)
        ");

        $this->addSql("INSERT INTO config_type (tag, name, type, description, module, module_dependant) 
            VALUES 
            ('app_name', 'Application Name', '" . ConfigType::TEXT_TYPE . "', '', 0, NULL),
            ('app_name_short', 'Application Short Name', '" . ConfigType::TEXT_TYPE . "', '', 0, NULL),
            ('logo_big', 'Large Logo', '" . ConfigType::SOURCE_TYPE . "', '', 0, NULL),
            ('logo_small', 'Small Logo', '" . ConfigType::SOURCE_TYPE . "', '', 0, NULL),
            ('favicon', 'Favicon', '" . ConfigType::SOURCE_TYPE . "', '', 0, NULL),
            ('primary_color', 'Primary Color', '" . ConfigType::COLOR_TYPE . "', '', 0, NULL),
            ('secondary_color', 'Secondary Color', '" . ConfigType::COLOR_TYPE . "', '', 0, NULL),
            ('days_notification', 'Days to Notify', '" . ConfigType::NUMBER_TYPE . "', '', 0, NULL),
            ('" . Invoice::ENTITY . "', 'Invoice Module', '" . ConfigType::BOOLEAN_TYPE . "', '', 1, NULL),
            ('calendar_interval', 'Calendar Interval', '" . ConfigType::TIME_TYPE . "', '', 0, NULL),
            ('complete_on_pay', 'Complete on Pay', '" . ConfigType::BOOLEAN_TYPE . "', '', 0, NULL),
            ('" . PaymentMethod::ENTITY . "', 'Payment Module', '" . ConfigType::BOOLEAN_TYPE . "', '', 1, NULL)
            
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM config_type WHERE tag IN ('app_name', 'app_name_short', 'primary_color', 'secondary_color', 'logo_big', 'logo_small', 'font_white', 'font_dark', 'bg_white', 'bg_dark', 'favicon', 'days_notification', '" . Invoice::ENTITY . "', 'calendar_interval', 'complete_on_pay', '" . PaymentMethod::ENTITY . "', 'flex_schedules_interval')");
        $this->addSql("DELETE FROM role_has_permission WHERE permission_id BETWEEN 1 AND 65");
        $this->addSql("DELETE FROM permission WHERE id BETWEEN 1 AND 65");
        $this->addSql("DELETE FROM permission_group WHERE id BETWEEN 1 AND 13");
        $this->addSql("DELETE FROM status WHERE id BETWEEN 1 AND 6");
        $this->addSql("DELETE FROM payment_method WHERE id BETWEEN 1 AND 3");
        $this->addSql("DELETE FROM role WHERE id BETWEEN 1 AND 3");
    }
}
