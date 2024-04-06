<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Config\ConfigType;
use App\Entity\Invoice\Invoice;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230913055820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $entity = Invoice::ENTITY;
        $this->addSql("
            INSERT INTO permission (id, group_id, label, action, admin_managed, module_dependant) 
            VALUES 
            (72, 8, 'Export', 'export', 0, '$entity')
        ");

        $this->addSql("INSERT INTO role_has_permission (role_id, permission_id)
            VALUES 
            (1,72),
            (2,72)
        ");


        $this->addSql("INSERT INTO config_type (tag, name, type, description, module, module_dependant, default_value) 
            VALUES 
            ('" . ConfigType::USER_NOMENCLATURE_TAG .   "', 'User nomenclature', '" .           ConfigType::RADIO_TYPE .    "', '', 0, NULL, 'user'),
            ('" . ConfigType::USER_NOMENCLATURE_TAG .   "', 'Professional nomenclature', '" .   ConfigType::RADIO_TYPE .    "', '', 0, NULL, 'professional'),
            ('" . ConfigType::CLIENT_NOMENCLATURE_TAG . "', 'Client nomenclature', '" .         ConfigType::RADIO_TYPE .    "', '', 0, NULL, 'client'),
            ('" . ConfigType::CLIENT_NOMENCLATURE_TAG . "', 'Patient nomenclature', '" .        ConfigType::RADIO_TYPE .    "', '', 0, NULL, 'patient'),
            ('" . ConfigType::BILLING_SERIE_TAG .       "', 'Serie', '" .                       ConfigType::TEXT_TYPE .     "', '', 0, '" . Invoice::ENTITY . "', NULL),
            ('" . ConfigType::BILLING_VAT_TAG .         "', 'VAT', '" .                         ConfigType::NUMBER_TYPE .   "', '', 0, '" . Invoice::ENTITY . "', NULL),
            ('" . ConfigType::BILLING_LOGO_TAG .        "', 'Logo', '" .                        ConfigType::SOURCE_TYPE .   "', '', 0, '" . Invoice::ENTITY . "', NULL),
            ('" . ConfigType::BILLING_COMPANY_NAME_TAG ."', 'Company Name', '" .                ConfigType::TEXT_TYPE .     "', '', 0, '" . Invoice::ENTITY . "', NULL),
            ('" . ConfigType::BILLING_ADDRESS_TAG .     "', 'Company Address', '" .             ConfigType::TEXT_TYPE .     "', '', 0, '" . Invoice::ENTITY . "', NULL),
            ('" . ConfigType::BILLING_FIC_TAG .         "', 'FIC', '" .                         ConfigType::TEXT_TYPE .     "', '', 0, '" . Invoice::ENTITY . "', NULL),
            ('" . ConfigType::BILLING_PHONE_TAG .       "', 'Company Phone', '" .               ConfigType::TEXT_TYPE .     "', '', 0, '" . Invoice::ENTITY . "', NULL)
        ");


    }

    public function down(Schema $schema): void
    {
        $tags = [
            ConfigType::USER_NOMENCLATURE_TAG,
            ConfigType::CLIENT_NOMENCLATURE_TAG,
            ConfigType::BILLING_SERIE_TAG,
            ConfigType::BILLING_VAT_TAG,
            ConfigType::BILLING_LOGO_TAG,
            ConfigType::BILLING_COMPANY_NAME_TAG,
            ConfigType::BILLING_ADDRESS_TAG,
            ConfigType::BILLING_FIC_TAG,
            ConfigType::BILLING_PHONE_TAG,
        ];

        $tagStr = "";
        foreach ($tags as $key => $tag){
            $tagStr .= "'" . $tag . "'";

            if ($key != array_key_last($tags)) {
                $tagStr .= ',';
            }
        }
        $this->addSql("DELETE FROM permission WHERE id = 72");
        $this->addSql("DELETE FROM role_has_permission WHERE permission_id = 72");
        $this->addSql("DELETE FROM config_type WHERE tag IN(" . $tagStr . ")");

    }
}
