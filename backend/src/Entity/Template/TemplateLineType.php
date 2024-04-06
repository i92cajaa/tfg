<?php

namespace App\Entity\Template;

use App\Entity\Document;
use App\Repository\ConfigRepository;
use App\Repository\TemplateLineTypeRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: TemplateLineTypeRepository::class)]
class TemplateLineType
{

    const SOURCE_TYPE = 'Archivo';
    const SHORT_TEXT_TYPE = 'Texto Corto';
    const TEXT_TYPE = 'Texto';
    const NUMBER_TYPE = 'Número';
    const COLOR_TYPE = 'Color';
    const TIME_TYPE = 'Hora';
    const DATE_TYPE = 'Fecha';
    const BOOLEAN_TYPE = 'Confirmación';
    const SELECT_TYPE = 'Selector';
    const TITLE_TYPE = 'Título';


    const TYPES = [
        'Title' => self::TITLE_TYPE,
        'Text' => self::TEXT_TYPE,
        'Short Text' => self::SHORT_TEXT_TYPE,
        'File' => self::SOURCE_TYPE,
        'Number' => self::NUMBER_TYPE,
        'Date' => self::DATE_TYPE,
        'Hour' => self::TIME_TYPE,
        'Color' => self::COLOR_TYPE,
        'Confirmation' => self::BOOLEAN_TYPE,
        'Selector' => self::SELECT_TYPE,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: TemplateType::class, inversedBy: 'templateLineTypes')]
    #[ORM\JoinColumn(name: "template_type_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private TemplateType $templateType;

    // Campos

    #[ORM\Column(type: 'string', length: 255, nullable:false)]
    private string $name = '';

    #[ORM\Column(type: 'string', nullable:false)]
    private string $type;

    #[ORM\Column(type: 'array', nullable:true)]
    private ?array $options = [];

    public function getId(): string
    {
        return $this->id;
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
     * @return TemplateLineType
     */
    public function setName(string $name): TemplateLineType
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
     * @return TemplateLineType
     */
    public function setType(string $type): TemplateLineType
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options ?: [];
    }

    /**
     * @param array $options
     * @return TemplateLineType
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return TemplateType
     */
    public function getTemplateType(): TemplateType
    {
        return $this->templateType;
    }

    /**
     * @param TemplateType $templateType
     * @return TemplateLineType
     */
    public function setTemplateType(TemplateType $templateType): TemplateLineType
    {
        $this->templateType = $templateType;
        return $this;
    }







}
