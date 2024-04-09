<?php

namespace App\Entity\Room;

use App\Entity\Center\Center;
use App\Entity\Schedule\Schedule;
use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
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

    #[ORM\ManyToOne(targetEntity: Center::class, inversedBy: 'rooms')]
    #[ORM\JoinColumn(name: "center_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Center $center;

    #[ORM\OneToMany(mappedBy:"room", targetEntity: Schedule::class, cascade:["persist", "remove"])]
    private array|Collection $schedules;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name:"floor", type:"integer", nullable:false)]
    private int $floor;

    #[ORM\Column(name:"number", type:"integer", nullable:false)]
    private int $number;

    #[ORM\Column(name:"capacity", type:"integer", nullable:false)]
    private int $capacity;

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
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Center
     */
    public function getCenter(): Center
    {
        return $this->center;
    }

    /**
     * @return array|Collection
     */
    public function getSchedules(): array|Collection
    {
        return $this->schedules;
    }

    /**
     * @return int
     */
    public function getFloor(): int
    {
        return $this->floor;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param Center $center
     * @return $this
     */
    public function setCenter(Center $center): Room
    {
        $this->center = $center;
        return $this;
    }

    /**
     * @param array|Collection $schedules
     * @return $this
     */
    public function setSchedules(array|Collection $schedules): Room
    {
        $this->schedules = $schedules;
        return $this;
    }

    /**
     * @param int $floor
     * @return $this
     */
    public function setFloor(int $floor): Room
    {
        $this->floor = $floor;
        return $this;
    }

    /**
     * @param int $number
     * @return $this
     */
    public function setNumber(int $number): Room
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @param int $capacity
     * @return $this
     */
    public function setCapacity(int $capacity): Room
    {
        $this->capacity = $capacity;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD SCHEDULE TO ROOM
     * ES: FUNCIÓN PARA AÑADIR HORARIO A SALA
     *
     * @param Schedule $schedule
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addSchedule(Schedule $schedule): Room
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE SCHEDULE TO ROOM
     * ES: FUNCIÓN PARA BORRAR HORARIO A SALA
     *
     * @param Schedule $schedule
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeSchedule(Schedule $schedule): Room
    {
        if ($this->schedules->contains($schedule)) {
            $this->schedules->removeElement($schedule);
        }

        return $this;
    }
    // ----------------------------------------------------------------
}