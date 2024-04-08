<?php

namespace App\Entity\Area;
use App\Entity\Center\Center;
use App\Repository\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
class Area
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

    #[ORM\OneToMany(mappedBy: "area", targetEntity: Center::class, cascade: ["persist", "remove"])]
    private array|Collection $centers;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name:"name", type:"string", length:255, nullable:false)]
    private string $name;

    #[ORM\Column(name:"city", type:"string", length:255, nullable:false)]
    private string $city;

    #[ORM\Column(name:"color", type:"string", length:255, nullable:false)]
    private string $color;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->centers = new ArrayCollection();
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
     * @return array|Collection
     */
    public function getCenters(): array|Collection
    {
        return $this->centers;
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
    public function getCity(): string
    {
        return $this->city;
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

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): Area
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param array|Collection $centers
     * @return $this
     */
    public function setCenters(array|Collection $centers): Area
    {
        $this->centers = $centers;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Area
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function setCity(string $city): Area
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @param string $color
     * @return $this
     */
    public function setColor(string $color): Area
    {
        $this->color = $color;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD CENTER TO THIS AREA
     * ES: FUNCIÓN PARA AÑADIR UN CENTRO AL ÁREA
     *
     * @param Center $center
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addCenter(Center $center): Area
    {
        if (!$this->centers->contains($center)) {
            $this->centers->add($center);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE CENTER FROM THIS AREA
     * ES: FUNCIÓN PARA BORRAR UN CENTRO DEL ÁREA
     *
     * @param Center $center
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeCenter(Center $center): Area
    {
        if ($this->centers->contains($center)) {
            $this->centers->removeElement($center);
        }

        return $this;
    }
    // ----------------------------------------------------------------
}