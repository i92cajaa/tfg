<?php

namespace App\Entity\Template;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\User\User;
use App\Repository\TemplateRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
class Template
{
    const ENTITY = 'template';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:true, onDelete: 'CASCADE')]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: TemplateType::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(name: "template_type_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private TemplateType $templateType;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", nullable:true, onDelete: 'CASCADE')]
    private ?Client $client;

    #[ORM\ManyToOne(targetEntity: Appointment::class, inversedBy: 'templates')]
    #[ORM\JoinColumn(name: "appointment_id", referencedColumnName:"id", nullable:true, onDelete: 'SET NULL')]
    private ?Appointment $appointment;

    // Campos

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $createdAt;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"template", targetEntity: TemplateLine::class, cascade:["persist", "remove"], orphanRemoval:true)]
    private Collection $templateLines;


    public function __construct()
    {
        $this->templateLines = new ArrayCollection();
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): Template
    {
        $this->user = $user;
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
     * @return Template
     */
    public function setCreatedAt(?DateTime $createdAt): Template
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection|TemplateLineType[]
     */
    public function getTemplateLineTypes(): Collection
    {
        return $this->templateLines;
    }

    public function addTemplateLine(TemplateLine $templateLine): self
    {
        if (!$this->templateLines->contains($templateLine)) {
            $this->templateLines[] = $templateLine;
            $templateLine->setTemplate($this);
        }

        return $this;
    }

    public function removeTemplateLine(TemplateLine $templateLine): self
    {
        if ($this->templateLines->removeElement($templateLine)) {
            // set the owning side to null (unless already changed)

        }

        return $this;
    }

    public function removeAllTemplateLines(): Template
    {
        foreach ($this->templateLines as $templateLine) {
            $this->templateLines->removeElement($templateLine);
        }
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
     * @return Template
     */
    public function setTemplateType(TemplateType $templateType): Template
    {
        $this->templateType = $templateType;
        return $this;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param Client|null $client
     * @return Template
     */
    public function setClient(?Client $client): Template
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return Appointment|null
     */
    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    /**
     * @param Appointment|null $appointment
     * @return Template
     */
    public function setAppointment(?Appointment $appointment): Template
    {
        $this->appointment = $appointment;
        return $this;
    }

    /**
     * @return UserHasRole[]|Collection
     */
    public function getTemplateLines()
    {
        return $this->templateLines;
    }

    /**
     * @param UserHasRole[]|Collection $templateLines
     * @return Template
     */
    public function setTemplateLines($templateLines)
    {
        $this->templateLines = $templateLines;
        return $this;
    }

    public function getTemplateLineValueByName(string $name): ?string
    {
        foreach ($this->templateLines as $templateLine){
            if($templateLine->getName() == $name){
                return $templateLine->getValue();
            }
        }

        return '';
    }

    public function getTemplateLineByName(string $name): ?TemplateLine
    {
        foreach ($this->templateLines as $templateLine){
            if($templateLine->getName() == $name){
                return $templateLine;
            }
        }

        return null;
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
