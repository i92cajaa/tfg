<?php

namespace App\Entity\Service;

use App\Entity\Appointment\AppointmentHasService;
use App\Entity\User\User;
use App\Entity\User\UserHasRole;
use App\Entity\User\UserHasService;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;


#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    const TYPE_PRINCIPAL = "Principal";
    const TYPE_SECONDARY = "Secundario";

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: Division::class, inversedBy: 'services')]
    private ?Division $division;

    // Campos

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $color;

    #[ORM\Column(type: 'float', nullable: false)]
    private float $price;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $iva;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $neededTime;

    #[ORM\Column(type: 'boolean', options: ["default" => 1])]
    private bool $active;

    #[ORM\Column(type: 'boolean', options: ["default" => 1])]
    private bool $ivaApplied;

    #[ORM\Column(type: 'boolean', options: ["default" => 0])]
    private bool $forAdmin;

    #[ORM\Column(type: 'boolean', options: ["default" => 0])]
    private bool $forClient;

    // Colecciones

    #[ORM\OneToMany(mappedBy: "service", targetEntity: UserHasService::class, cascade: ["persist", "remove"])]
    private Collection $professionals;

    #[ORM\OneToMany(mappedBy: "service", targetEntity: AppointmentHasService::class, cascade: ["persist", "remove"])]
    private Collection $appointments;

    public function __construct()
    {

        $this->name = '';
        $this->color = null;
        $this->price = 0;
        $this->iva = null;
        $this->neededTime = null;
        $this->ivaApplied = false;
        $this->forAdmin = false;
        $this->forClient = false;
        $this->active = true;
        $this->division = null;

        $this->professionals = new ArrayCollection();
        $this->appointments = new ArrayCollection();

    }

    public function __toString(): string
    {
        return $this->id;
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return bool
     */
    public function isForAdmin(): bool
    {
        return $this->forAdmin;
    }

    /**
     * @param bool $forAdmin
     */
    public function setForAdmin(bool $forAdmin): void
    {
        $this->forAdmin = $forAdmin;
    }

    public function isForClient(): bool
    {
        return $this->forClient;
    }

    public function setForClient(bool $forClient): void
    {
        $this->forClient = $forClient;
    }




    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }


    public function getProfessionalRelations(): ?Collection
    {
        return $this->professionals;
    }

    public function getProfessionals(): array
    {
        $users = [];
        /** @var UserHasService $serviceHasUser */
        foreach ($this->professionals as $serviceHasUser) {
            $users[] = $serviceHasUser->getUser();
        }

        return $users;
    }

    public function addProfessional(User $professional): self
    {
        $userHasService = (new UserHasService())
            ->setService($this)
            ->setUser($professional);

        if (!$this->professionals->contains($userHasService)) {
            $this->professionals->add($userHasService);
        }

        return $this;
    }

    public function removeProfessional(User $professional): self
    {

        /** @var UserHasService $userHasService */
        foreach ($this->professionals as $userHasService) {

            if ($userHasService->getUser() == $professional) {

                $this->professionals->removeElement($userHasService);
            }
        }

        return $this;
    }

    public function removeProfessionals(): self
    {
        /** @var UserHasService $userHasService */
        foreach ($this->professionals as $userHasService) {
            $this->professionals->removeElement($userHasService);
        }

        return $this;
    }

    public function getAppointments(): array
    {
        $appointments = [];
        foreach ($this->appointments as $appointment) {
            $appointments[] = $appointment;
        }
        return $appointments;
    }


    public function getIva(): ?float
    {
        return $this->iva;
    }

    public function setIva(?float $iva): self
    {
        $this->iva = $iva;
        return $this;
    }

    public function getIvaApplied(): bool
    {
        return $this->ivaApplied;
    }

    public function setIvaApplied(bool $ivaApplied): self
    {
        $this->ivaApplied = $ivaApplied;
        return $this;
    }

    public function getDivision(): ?Division
    {
        return $this->division;
    }

    public function setDivision(?Division $division): self
    {
        $this->division = $division;

        return $this;
    }

    public function getTotalPrice(bool $iva = false): float|int|null
    {
        if ($iva) {
            if ($this->getIvaApplied()) {
                return $this->getPrice();
            } else {
                $ivaType = 1 + floatval($this->getIva()) / 100;
                return $this->getPrice() * $ivaType;
            }

        } else {
            if ($this->getIvaApplied()) {
                $ivaType = 1 + floatval($this->getIva()) / 100;
                return $this->getPrice() / $ivaType;
            } else {
                return $this->getPrice();
            }
        }
    }

    public function getNeededTime(): ?int
    {
        return $this->neededTime;
    }

    public function setNeededTime(?int $neededTime): self
    {
        $this->neededTime = $neededTime;
        return $this;
    }

}
