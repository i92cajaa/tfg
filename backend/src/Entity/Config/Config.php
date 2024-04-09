<?php

namespace App\Entity\Config;

use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use App\Repository\ConfigRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ConfigRepository::class)]
class Config
{

    // ----------------------------------------------------------------
    // Constants
    // ----------------------------------------------------------------

    const ENTITY = 'config';

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name:"name", type:"string", length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name:"tag", type:"string", nullable: false)]
    private string $tag;

    #[ORM\Column(name:"description", type:"string", length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column(name:"value", type:"text", nullable: true)]
    private ?string $value;

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

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
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param string $name
     * @return Config
     */
    public function setName(string $name): Config
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $tag
     * @return Config
     */
    public function setTag(string $tag): Config
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @param string|null $description
     * @return Config
     */
    public function setDescription(?string $description): Config
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string|null $value
     * @return Config
     */
    public function setValue(?string $value): Config
    {
        $this->value = $value;
        return $this;
    }

}
