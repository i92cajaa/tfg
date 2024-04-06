<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Config\ConfigType;
use App\Entity\Meeting\Meeting;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230913061653 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO config_type (tag, name, type, description, module, module_dependant, order_number) 
    VALUES 
    ('" . ConfigType::MEETING_MODULE_TAG . "', 'Meeting Module', '" . ConfigType::BOOLEAN_TYPE . "', '', 1, NULL, 0),
    ('" . ConfigType::MEETING_CLIENT_ID . "', 'Microsoft Azure Client ID', '" . ConfigType::PASSWORD_TYPE . "', '', 0, '" . Meeting::ENTITY . "', 20),
    ('" . ConfigType::MEETING_CLIENT_SECRET_ID . "', 'Microsoft Azure Client Secret ID', '" . ConfigType::PASSWORD_TYPE . "', '', 0, '" . Meeting::ENTITY . "', 21),
    ('" . ConfigType::MEETING_TENANT_ID . "', 'Microsoft Azure Tenant ID', '" . ConfigType::PASSWORD_TYPE . "', '', 0, '" . Meeting::ENTITY . "', 22),
    ('" . ConfigType::MEETING_SERVICE_USER_ID . "', 'Microsoft Azure Service User ID', '" . ConfigType::PASSWORD_TYPE . "', '', 0, '" . Meeting::ENTITY . "', 23)
");
    }

    public function down(Schema $schema): void
    {
        $tags = [
            ConfigType::MEETING_MODULE_TAG,
            ConfigType::MEETING_CLIENT_ID,
            ConfigType::MEETING_CLIENT_SECRET_ID,
            ConfigType::MEETING_TENANT_ID,
            ConfigType::MEETING_SERVICE_USER_ID
        ];

        $tagStr = "";
        foreach ($tags as $key => $tag){
            $tagStr .= "'" . $tag . "'";

            if ($key != array_key_last($tags)) {
                $tagStr .= ',';
            }
        }

        $this->addSql('DELETE FROM config_type WHERE tag IN(' . $tagStr . ')');
    }
}
