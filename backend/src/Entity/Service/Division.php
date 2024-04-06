<?php

namespace App\Entity\Service;


use App\Entity\ExtraAppointmentField\ExtraAppointmentFieldType;
use App\Repository\DivisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: DivisionRepository::class)]
class Division
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Campos

    #[ORM\Column(type:'string', length: 255, nullable: false)]
    private string $name;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"division", targetEntity: Service::class)]
    private Collection $services;

    #[ORM\OneToMany(mappedBy:"division", targetEntity: ExtraAppointmentFieldType::class)]
    private Collection $extraAppointmentFieldTypes;

    public function __construct()
    {
        $this->name = '';

        $this->services = new ArrayCollection();
        $this->extraAppointmentFieldTypes = new ArrayCollection();
    }

    public function getId(): string
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
     * @return Collection|Service[]
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setDivision($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getDivision() === $this) {
                $service->setDivision(null);
            }
        }

        return $this;
    }
}
