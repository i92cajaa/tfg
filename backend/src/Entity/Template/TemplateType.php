<?php

namespace App\Entity\Template;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\User\User;
use App\Repository\TemplateTypeRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity(repositoryClass: TemplateTypeRepository::class)]
class TemplateType
{
    const ENTITY = 'template';
    const ENTITIES = [
        'Mentoria' => Appointment::ENTITY,
        'Proyectos' => Client::ENTITY,
        'Mentores' => User::ENTITY,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Campos

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $entity;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $createdAt;

    #[ORM\Column(type: 'boolean', options:["default"=>"1"])]
    private ?bool $active;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"templateType", targetEntity: TemplateLineType::class, cascade:["persist", "remove"], orphanRemoval:true)]
    private Collection $templateLineTypes;

    #[ORM\OneToMany(mappedBy:"templateType", targetEntity: Template::class)]
    private Collection $templates;


    public function __construct()
    {
        $this->name = '';
        $this->entity = Appointment::ENTITY;
        $this->description = null;

        $this->templateLineTypes = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->setActive(true);
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create('now'));
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param mixed $entity
     * @return TemplateType
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }



    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     * @return TemplateType
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return TemplateType
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return ?DateTime
     */
    public function getCreatedAt(): ?DateTime
    {
        return UTCDateTime::format($this->createdAt);
    }

    /**
     * @param ?DateTime $createdAt
     * @return TemplateType
     */
    public function setCreatedAt(?DateTime $createdAt): TemplateType
    {
        $this->createdAt = UTCDateTime::setUTC($createdAt);
        return $this;
    }

    /**
     * @return Collection|TemplateLineType[]
     */
    public function getTemplateLineTypes(): Collection
    {
        return $this->templateLineTypes;
    }

    public function addTemplateLineType(TemplateLineType $templateLine): self
    {
        if (!$this->templateLineTypes->contains($templateLine)) {
            $this->templateLineTypes[] = $templateLine;
            $templateLine->setTemplateType($this);
        }

        return $this;
    }

    public function removeTemplateLineType(TemplateLineType $templateLine): self
    {
        if ($this->templateLineTypes->contains($templateLine)) {
            // set the owning side to null (unless already changed)
            $this->templateLineTypes->removeElement($templateLine);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {

        foreach (self::ENTITIES as $name => $entity) {
            if($entity == $this->getEntity()){
                return $name;
            }
        }
        return '';
    }

    public function getTemplateLineOptions(){
        $options = [];

        /** @var TemplateLineType $templateLineType */
        foreach ($this->templateLineTypes as $templateLineType){
            $options = array_merge($options, $templateLineType->getOptions());
        }

        return $options;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    /*
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'name',
        ]));
    }
    */
}
