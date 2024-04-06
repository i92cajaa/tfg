<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231204085730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE appointment a
                            SET a.center_id = (
                            SELECT sub.center_id
                            FROM (
                                SELECT ua.appointment_id, u.center_id
                                FROM appointment_user ua
                                LEFT JOIN user u ON ua.user_id = u.id
                                WHERE 1
                                GROUP BY ua.appointment_id
                            ) AS sub
                            WHERE sub.appointment_id = a.id
                            )
                            WHERE a.id IN (
                                SELECT ua.appointment_id
                                FROM appointment_user ua
                                LEFT JOIN user u ON ua.user_id = u.id
                                WHERE 1
                                GROUP BY ua.appointment_id)');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('');

    }
}
