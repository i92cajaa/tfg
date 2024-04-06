<?php

namespace App\Entity\ExtraAppointmentField;

use App\Entity\Service\Division;
use App\Entity\Document\Document;

use App\Repository\ExtraAppointmentFieldTypeRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ExtraAppointmentFieldTypeRepository::class)]
class ExtraAppointmentFieldType
{

    const SOURCE_TYPE = 'File';
    const TEXT_TYPE = 'Text';
    const NUMBER_TYPE = 'Number';
    const COLOR_TYPE = 'Color';
    const TIME_TYPE = 'Hour';
    const DATE_TYPE = 'Date';
    const BOOLEAN_TYPE = 'Confirmation';
    const SELECT_TYPE = 'Selector';

    const POSITION_FOOTER = 'footer';
    const POSITION_HEADER = 'header';

    const TYPES = [
        'Text' => self::TEXT_TYPE,
        'File' => self::SOURCE_TYPE,
        'Number' => self::NUMBER_TYPE,
        'Date' => self::DATE_TYPE,
        'Hour' => self::TIME_TYPE,
        'Color' => self::COLOR_TYPE,
        'Confirmation' => self::BOOLEAN_TYPE,
        'Selector' => self::SELECT_TYPE,
    ];


    const POSITIONS = [
        'Header' => self::POSITION_HEADER,
        'Footer' => self::POSITION_FOOTER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: Division::class, inversedBy: 'extraAppointmentFieldTypes')]
    #[ORM\JoinColumn(nullable:true, onDelete: 'SET NULL')]
    private ?Division $division;

    // Campos

    #[ORM\Column(type: 'string', length:255, nullable: false)]
    private string $name ;

    #[ORM\Column(type: 'string', length:255, nullable: false)]
    private string $type;

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $options;

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $position;

    public function __construct()
    {
        $this->name = '';
        $this->division = null;
        $this->type = '';
        $this->options = [];
        $this->description = null;
        $this->position = null;
    }

    /**
     * @return int
     */
    public function getId(): int
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
     * @return ExtraAppointmentFieldType
     */
    public function setName(string $name): ExtraAppointmentFieldType
    {
        $this->name = $name;
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
     * @return ExtraAppointmentFieldType
     */
    public function setDescription(?string $description): ExtraAppointmentFieldType
    {
        $this->description = $description;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getPosition(): ?string
    {
        return $this->position ?: self::POSITION_FOOTER;
    }

    /**
     * @param string|null $position
     * @return ExtraAppointmentFieldType
     */
    public function setPosition(?string $position): ExtraAppointmentFieldType
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return Division|null
     */
    public function getDivision(): ?Division
    {
        return $this->division;
    }

    /**
     * @param Division|null $division
     * @return ExtraAppointmentFieldType
     */
    public function setDivision(?Division $division): ExtraAppointmentFieldType
    {
        $this->division = $division;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return ExtraAppointmentFieldType
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
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
     * @return ExtraAppointmentFieldType
     */
    public function setType(string $type): ExtraAppointmentFieldType
    {
        $this->type = $type;
        return $this;
    }





}
