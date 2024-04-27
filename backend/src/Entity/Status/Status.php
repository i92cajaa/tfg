<?php

namespace App\Entity\Status;

use App\Entity\Appointment\Appointment;
use App\Entity\ClientRequest\ClientRequest;
use App\Entity\Schedule\Schedule;
use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{

    // ----------------------------------------------------------------
    // Constants
    // ----------------------------------------------------------------

    const STATUS_AVAILABLE = 1;
    const STATUS_FULL = 2;
    const STATUS_CANCELED = 3;
    const STATUS_COMPLETED = 4;

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    #[ORM\OneToMany(mappedBy:"status", targetEntity: Schedule::class)]
    private array|Collection $schedules;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(type:"string", length: 180, nullable: false)]
    private string $name;

    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $color;

    #[ORM\Column(type:"string", length: 30, nullable: false)]
    private string $entityType;

    #[ORM\Column(type:"integer", length: 30, nullable: false)]
    private int $statusOrder;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->schedules = new ArrayCollection();
    }

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array|Collection
     */
    public function getSchedules(): array|Collection
    {
        return $this->schedules;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return int
     */
    public function getStatusOrder(): int
    {
        return $this->statusOrder;
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param array|Collection $schedules
     * @return $this
     */
    public function setSchedules(array|Collection $schedules): Status
    {
        $this->schedules = $schedules;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Status
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $color
     * @return $this
     */
    public function setColor(string $color): Status
    {
        $this->color = $color;
        return $this;
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
     * @param string $entityType
     * @return Status
     */
    public function setEntityType(string $entityType): Status
    {
        $this->entityType = $entityType;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD SCHEDULE TO STATUS
     * ES: FUNCIÓN PARA AÑADIR HORARIO A ESTADO
     *
     * @param Schedule $schedule
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addSchedule(Schedule $schedule): Status
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE SCHEDULE FROM STATUS
     * ES: FUNCIÓN PARA BORRAR HORARIO DE ESTADO
     *
     * @param Schedule $schedule
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeSchedule(Schedule $schedule): Status
    {
        if ($this->schedules->contains($schedule)) {
            $this->schedules->removeElement($schedule);
        }

        return $this;
    }
    // ----------------------------------------------------------------

}
