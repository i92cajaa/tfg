<?php

namespace App\Entity\Center;

use App\Entity\Area\Area;
use App\Entity\Document\Document;
use App\Entity\User\User;
use App\Repository\CenterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CenterRepository::class)]
class Center
{

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $logo = null;

    #[ORM\OneToMany(mappedBy:"center", targetEntity: User::class, cascade:["persist", "remove"])]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: Area::class, inversedBy: 'centers')]
    #[ORM\JoinColumn(name: "area_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Area $area;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name:"name", type:"string", length:255, nullable:false)]
    private string $name;

    #[ORM\Column(name:"address", type:"string", length:255, nullable:false)]
    private string $address;

    #[ORM\Column(name:"phone", type:"string", length:255, nullable:false)]
    private string $phone;

    #[ORM\Column(name:"color", type: 'string', length:255, nullable: false)]
    private string $color;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

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
     * @return Document|null
     */
    public function getLogo(): ?Document
    {
        return $this->logo;
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @return Area
     */
    public function getArea(): Area
    {
        return $this->area;
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
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------
}
