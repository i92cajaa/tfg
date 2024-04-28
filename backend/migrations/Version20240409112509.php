<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Config\ConfigType;
use App\Entity\Schedule\Schedule;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409112509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO role(`id`, `name`, `admin`) VALUES (1,"Super admin",1)');
        $this->addSql('INSERT INTO role(`id`, `name`, `admin`) VALUES (2,"Admin",1)');
        $this->addSql('INSERT INTO role(`id`, `name`, `admin`) VALUES (3,"Profesor",0)');

        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (1, 'Disponible', '#00cfe8', '" . Schedule::ENTITY . "', 0)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (2, 'Llena', '#ff9f43', '" . Schedule::ENTITY . "', 1)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (3, 'Cancelada', '#325334', '" . Schedule::ENTITY . "', 2)");
        $this->addSql("INSERT INTO status(id, name, color, entity_type, status_order) VALUES (4, 'Completada', '#ff9f43', '" . Schedule::ENTITY . "', 3)");

        $this->addSql("INSERT INTO permission_group (id, name, label) 
                            VALUES 
                            (1, 'users', 'Users'),
                            (2, 'roles', 'Roles'),
                            (3, 'clients', 'Clients'),
                            (4, 'bookings', 'Bookings'),
                            (5, 'lessons', 'Lessons'),
                            (6, 'schedules', 'Schedules'),
                            (7, 'areas', 'Areas'),
                            (8, 'centers', 'Centers'),
                            (9, 'configs', 'Configuration'),
                            (10, 'rooms', 'Rooms')
                                                    ");

        $this->addSql("INSERT INTO permission (id, group_id, label, action, admin_managed) 
                            VALUES 
                            (1, 1, 'List', 'list', 0),
                            (2, 1, 'Show', 'show', 0),
                            (3, 1, 'Create', 'create', 0),
                            (4, 1, 'Edit', 'edit', 0),
                            (5, 1, 'Delete', 'delete', 0),
                            
                            (6, 2, 'List', 'list', 1),
                            (7, 2, 'Show', 'show', 1),
                            (8, 2, 'Create', 'create', 1),
                            (9, 2, 'Edit', 'edit', 1),
                            (10, 2, 'Delete', 'delete', 1),
                            
                            (11, 3, 'List', 'list', 0),
                            (12, 3, 'Show', 'show', 0),
                            (13, 3, 'Create', 'create', 0),
                            (14, 3, 'Edit', 'edit', 0),
                            (15, 3, 'Delete', 'delete', 0),
                            
                            (16, 4, 'List', 'list', 0),
                            (17, 4, 'Show', 'show', 0),
                            (18, 4, 'Create', 'create', 0),
                            (19, 4, 'Edit', 'edit', 0),
                            (20, 4, 'Delete', 'delete', 0),
                            
                            (21, 5, 'List', 'list', 0),
                            (22, 5, 'Show', 'show', 0),
                            (23, 5, 'Create', 'create', 0),
                            (24, 5, 'Edit', 'edit', 0),
                            (25, 5, 'Delete', 'delete', 0),
                            
                            (26, 6, 'List', 'list', 0),
                            (27, 6, 'Show', 'show', 0),
                            (28, 6, 'Create', 'create', 0),
                            (29, 6, 'Edit', 'edit', 0),
                            (30, 6, 'Delete', 'delete', 0),
                            
                            (31, 7, 'List', 'list', 1),
                            (32, 7, 'Show', 'show', 1),
                            (33, 7, 'Create', 'create', 1),
                            (34, 7, 'Edit', 'edit', 1),
                            (35, 7, 'Delete', 'delete', 1),
                            
                            (36, 8, 'List', 'list', 1),
                            (37, 8, 'Show', 'show', 1),
                            (38, 8, 'Create', 'create', 1),
                            (39, 8, 'Edit', 'edit', 1),
                            (40, 8, 'Delete', 'delete', 1),
                            
                            (41, 9, 'Edit', 'edit', 1),
                            
                            (42, 10, 'List', 'list', 1),
                            (43, 10, 'Show', 'show', 1),
                            (44, 10, 'Create', 'create', 1),
                            (45, 10, 'Edit', 'edit', 1),
                            (46, 10, 'Delete', 'delete', 1),
                            
                            (47, 4, 'Export', 'export', 0)
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
                            (1,42),
                            (1,43),
                            (1,44),
                            (1,45),
                            (1,46),
                            (1,47),
                            
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
                            (2,37),
                            (2,42),
                            (2,43),
                            (2,44),
                            (2,45),
                            (2,46),
                            
                            (3,21),
                            (3,22),
                            (3,23),
                            (3,24),
                            (3,26),
                            (3,27),
                            (3,28),
                            (3,29)
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
            ('calendar_interval', 'Calendar Interval', '" . ConfigType::TIME_TYPE . "', '', 0, NULL)
        ");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM config_type WHERE tag IN ('app_name', 'app_name_short', 'primary_color', 'secondary_color', 'logo_big', 'logo_small', 'font_white', 'font_dark', 'bg_white', 'bg_dark', 'favicon', 'days_notification', 'calendar_interval', 'complete_on_pay', 'flex_schedules_interval')");
        $this->addSql("DELETE FROM role_has_permission WHERE permission_id BETWEEN 1 AND 50");
        $this->addSql("DELETE FROM permission WHERE id BETWEEN 1 AND 50");
        $this->addSql("DELETE FROM permission_group WHERE id BETWEEN 1 AND 10");
        $this->addSql("DELETE FROM status WHERE id BETWEEN 1 AND 4");
        $this->addSql("DELETE FROM role WHERE id BETWEEN 1 AND 3");
    }
}
