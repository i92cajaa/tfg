<?php

namespace App\Entity\Status;

use App\Entity\Appointment\Appointment;
use App\Entity\ClientRequest\ClientRequest;
use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    const STATUS_REQUEST = 1;
    const STATUS_ASSIGNED = 2;
    const STATUS_CONFIRMED = 3;

    const STATUS_TASK_PENDING = 4;
    const STATUS_TASK_IN_PROGRESS = 5;
    const STATUS_TASK_COMPLETED = 6;

    const STATUS_CANCELED_DIRECTOR = 7;
    const STATUS_CANCELED_MENTOR = 8;
    const STATUS_COMPLETED = 9;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    // Campos

    #[ORM\Column(type:"string", length: 180, nullable: false)]
    private string $name;

    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $color;

    #[ORM\Column(type:"string", length: 30, nullable: false)]
    private string $entityType;

    #[ORM\Column(type:"integer", length: 30, nullable: false)]
    private int $statusOrder;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"statusType", targetEntity: Appointment::class)]
    private Collection $appointments;

    #[ORM\OneToMany(mappedBy:"status", targetEntity: ClientRequest::class)]
    private Collection $clientRequests;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
        $this->clientRequests = new ArrayCollection();
    }

    public function getId(): ?int
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
     * @return int
     */
    public function getStatusOrder(): int
    {
        return $this->statusOrder;
    }

    /**
     * @param int $statusOrder
     * @return Status
     */
    public function setStatusOrder(int $statusOrder): Status
    {
        $this->statusOrder = $statusOrder;
        return $this;
    }



    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @param string $entityType
     * @return Status
     */
    public function setEntityType(string $entityType): Status
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color):self
    {
        $this->color = $color;
        return $this;
    }

    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments[] = $appointment;
            $appointment->setStatusType($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getStatusType() === $this) {
                $appointment->setStatusType(null);
            }
        }

        return $this;
    }
}
