<?php

namespace App\Entity\Center;

use App\Entity\Client\Client;
use App\Entity\Document\Document;
use App\Repository\CenterRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CenterRepository::class)]
class Center
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $logo =null;

    #[ORM\OneToMany(mappedBy:"center", targetEntity: Client::class, cascade:["persist", "remove"])]
    private Collection $clients;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $color = null;


public function __construct()
{
//    $this->logo = null;
}

    /**
     * @return Collection
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    /**
     * @param Collection $clients
     */
    public function setClients(Collection $clients): void
    {
        $this->clients = $clients;
    }

    /**
     * @return Document|null
     */
    public function getLogo(): ?Document
    {
        return $this->logo;
    }

    /**
     * @param Document|null $logo
     */
    public function setLogo(?Document $logo): void
    {
        $this->logo = $logo;
    }



    /**
     * @return string|null
     */
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }





    public function getFullName(){

        return $this->getName() .' ('.$this->getCity().')';
    }


}
