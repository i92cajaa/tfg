<?php

namespace App\Entity\Config;

use App\Entity\Document\Document;
use App\Entity\Invoice\Invoice;
use App\Entity\Meeting\Meeting;
use App\Entity\Payment\PaymentMethod;
use App\Entity\Appointment\AppointmentModel;
use App\Repository\ConfigTypeRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ConfigTypeRepository::class)]
class ConfigType
{
    const SOURCE_TYPE = 'file';
    const TEXT_TYPE = 'text';
    const NUMBER_TYPE = 'number';
    const COLOR_TYPE = 'color';
    const RADIO_TYPE = 'radio';
    const TIME_TYPE = 'time';
    const BOOLEAN_TYPE = 'checkbox';
    const EVAL_TYPE = 'eval';
    const PASSWORD_TYPE = 'password';


    const PRIMARY_COLOR_TAG = 'primary_color';
    const SECONDARY_COLOR_TAG = 'secondary_color';
    const LOGO_BIG_TAG = 'logo_big';
    const LOGO_SMALL_TAG = 'logo_small';
    const FAVICON_TAG = 'favicon';
    const APP_NAME_TAG = 'app_name';
    const SHORT_COMPANY_NAME_TAG = 'app_name_short';
    const DAYS_NOTIFICATION_TAG = 'days_notification';
    const SMTP_USER_TAG = 'smtp_user';
    const SMTP_PASSWORD_TAG = 'smtp_password';
    const USER_NOMENCLATURE_TAG = 'user_nomenclature';
    const CLIENT_NOMENCLATURE_TAG = 'client_nomenclature';
    const BILLING_MODULE_TAG = Invoice::ENTITY;
    const BILLING_SERIE_TAG = 'invoice_serie';
    const BILLING_USER_SERIE_TAG = 'invoice_user_serie';
    const BILLING_VAT_TAG = 'invoice_vat';
    const BILLING_LOGO_TAG = 'invoice_logo';
    const BILLING_COMPANY_NAME_TAG = 'invoice_company_name';
    const BILLING_ADDRESS_TAG = 'invoice_address';
    const BILLING_FIC_TAG = 'invoice_fic';
    const BILLING_PHONE_TAG = 'invoice_phone';
    const CALENDAR_INTERVAL_TAG = 'calendar_interval';
    const COMPLETE_ON_PAY_TAG = 'complete_on_pay';
    const PAYMENT_MODULE_TAG = PaymentMethod::ENTITY;

    const MEETING_MODULE_TAG = Meeting::ENTITY;
    const MEETING_CLIENT_ID = 'meeting_client_id';
    const MEETING_CLIENT_SECRET_ID = 'meeting_client_secret_id';
    const MEETING_TENANT_ID = 'meeting_tenant_id';
    const MEETING_SERVICE_USER_ID = 'meeting_service_user_id';

    const MODULES = [
        Invoice::ENTITY,
        PaymentMethod::ENTITY,
        Meeting::ENTITY,
    ];


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;


    // Campos

    #[ORM\Column(name:"name", type:"string", length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name:"tag", type:"string", nullable: false)]
    private string $tag;

    #[ORM\Column(name:"type", type:"string", nullable: false)]
    private string $type;

    #[ORM\Column(name:"description", type:"string", length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column(name:"default_value", type:"string", length: 255, nullable: true)]
    private ?string $defaultValue;

    #[ORM\Column(name:"module", type:"boolean", length: 255, nullable: false, options:["default"=>"0"])]
    private bool $module = false;

    #[ORM\Column(name:"module_dependant", type:"string", length: 255, nullable: true)]
    private ?string $moduleDependant = null;

    #[ORM\Column(name:"order_number", type:"integer", length: 255, nullable: true)]
    private ?int $order = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getOrder(): ?int
    {
        return $this->order;
    }

    /**
     * @param int|null $order
     * @return ConfigType
     */
    public function setOrder(?int $order): ConfigType
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * @param string|null $defaultValue
     * @return ConfigType
     */
    public function setDefaultValue(?string $defaultValue): ConfigType
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return bool
     */
    public function isModule(): bool
    {
        return $this->module;
    }

    /**
     * @param bool $module
     * @return ConfigType
     */
    public function setModule(bool $module): ConfigType
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ConfigType
     */
    public function setName(string $name): ConfigType
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ConfigType
     */
    public function setType(string $type): ConfigType
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     * @return ConfigType
     */
    public function setTag(string $tag): ConfigType
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return ConfigType
     */
    public function setDescription(?string $description): ConfigType
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getModuleDependant(): ?string
    {
        return $this->moduleDependant;
    }

    /**
     * @param string|null $moduleDependant
     * @return ConfigType
     */
    public function setModuleDependant(?string $moduleDependant): ConfigType
    {
        $this->moduleDependant = $moduleDependant;
        return $this;
    }



}
