<?php

namespace App\Entity\Template;

use App\Entity\Document;
use App\Repository\ConfigRepository;
use App\Repository\TemplateLineRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: TemplateLineRepository::class)]
class TemplateLine
{

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: Template::class, inversedBy: 'templateLines')]
    #[ORM\JoinColumn(name: "template_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Template $template;

    // Campos

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name = '';

    #[ORM\Column(type: 'string', nullable: false)]
    private string $type;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value = null;


    /**
     * @return string
     */
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
     * @return TemplateLine
     */
    public function setName(string $name): TemplateLine
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
     * @return TemplateLine
     */
    public function setType(string $type): TemplateLine
    {
        $this->type = $type;
        return $this;
    }



    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return TemplateLine
     */
    public function setValue(?string $value): TemplateLine
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    /**
     * @param Template $template
     * @return TemplateLine
     */
    public function setTemplate(Template $template): TemplateLine
    {
        $this->template = $template;
        return $this;
    }



}
