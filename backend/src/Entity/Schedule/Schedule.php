<?php

namespace App\Entity\Schedule;

use App\Entity\Class\Lesson;
use App\Entity\Client\Booking;
use App\Entity\Room\Room;
use App\Entity\Status\Status;
use App\Repository\ScheduleRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{

    // ----------------------------------------------------------------
    // Constants
    // ----------------------------------------------------------------

    const ENTITY = 'schedule';

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

    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(name: "lesson_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Lesson $lesson;

    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(name: "status_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Status $status;

    #[ORM\ManyToOne(targetEntity: Room::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(name: "room_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Room $room;

    #[ORM\OneToMany(mappedBy:"schedule", targetEntity: Booking::class, cascade:["persist", "remove"])]
    private array|Collection $bookings;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name:"date_from", type:"datetime", nullable:false)]
    private DateTime $dateFrom;

    #[ORM\Column(name:"date_to", type:"datetime", nullable:false)]
    private DateTime $dateTo;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
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
     * @return Lesson
     */
    public function getLesson(): Lesson
    {
        return $this->lesson;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @return Room
     */
    public function getRoom(): Room
    {
        return $this->room;
    }

    /**
     * @return array|Collection
     */
    public function getBookings(): array|Collection
    {
        return $this->bookings;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateFrom(): \DateTimeInterface
    {
        return UTCDateTime::format($this->dateFrom);
    }


    public function getDateTo(): \DateTimeInterface
    {
        return UTCDateTime::format($this->dateTo);
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param Lesson $lesson
     * @return $this
     */
    public function setLesson(Lesson $lesson): Schedule
    {
        $this->lesson = $lesson;
        return $this;
    }

    /**
     * @param Status $status
     * @return $this
     */
    public function setStatus(Status $status): Schedule
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param Room $room
     * @return $this
     */
    public function setRoom(Room $room): Schedule
    {
        $this->room = $room;
        return $this;
    }

    /**
     * @param array|Collection $bookings
     * @return $this
     */
    public function setBookings(array|Collection $bookings): Schedule
    {
        $this->bookings = $bookings;
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $dateFrom
     * @return $this
     */
    public function setDateFrom(?\DateTimeInterface $dateFrom): Schedule
    {
        $this->dateFrom = UTCDateTime::setUTC($dateFrom);
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $dateTo
     * @return $this
     */
    public function setDateTo(?\DateTimeInterface $dateTo): Schedule
    {
        $this->dateTo = UTCDateTime::setUTC($dateTo);
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD A BOOKING TO A SCHEDULE
     * ES: FUNCIÓN PARA AÑADIR UNA RESERVA A UN HORARIO
     *
     * @param Booking $booking
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addBooking(Booking $booking): Schedule
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE A BOOKING FROM A SCHEDULE
     * ES: FUNCIÓN PARA BORRAR UNA RESERVA DE UN HORARIO
     *
     * @param Booking $booking
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeBooking(Booking $booking): Schedule
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CHECK IF A SCHEDULE IS FULL
     * ES: FUNCIÓN PARA COMPROBAR SI UN HORARIO ESTÁ COMPLETO
     *
     * @return bool
     */
    // ----------------------------------------------------------------
    public function isFull(): bool
    {
        if (count($this->bookings) === $this->room->getCapacity()) {
            return true;
        }

        return false;
    }
    // ----------------------------------------------------------------
}