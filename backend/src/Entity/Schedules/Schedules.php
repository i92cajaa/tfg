<?php

namespace App\Entity\Schedules;

use App\Entity\Appointment\Appointment;
use App\Entity\User\User;
use App\Repository\SchedulesRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;


#[ORM\Entity(repositoryClass: SchedulesRepository::class)]
class Schedules
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'datetime')]
    private DateTime $timeFrom;

    #[ORM\Column(type: 'datetime')]
    private Datetime $timeTo;


    #[ORM\Column(type: 'smallint')]
    private int $weekDay;

    #[ORM\OneToMany(mappedBy:"schedule", targetEntity: Appointment::class, cascade:["persist"])]
    private Collection $appointments;

    #[ORM\Column(type:"boolean", options:["default" => "1"])]
    private bool $status;

    #[ORM\Column(type:"boolean", nullable:true)]
    private ?bool $fixed;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTimeFrom(): ?DateTime
    {
        return UTCDateTime::format($this->timeFrom);
    }

    public function setTimeFrom(DateTime $timeFrom): self
    {
        $this->timeFrom = UTCDateTime::setUTC($timeFrom);

        return $this;
    }

    public function getTimeTo(): ?DateTime
    {
        return UTCDateTime::format($this->timeTo);
    }

    public function setTimeTo(DateTime $timeTo): self
    {
        $this->timeTo = UTCDateTime::setUTC($timeTo);

        return $this;
    }

    public function getWeekDay(): ?int
    {
        return $this->weekDay;
    }

    public function setWeekDay(int $weekDay): self
    {
        $this->weekDay = $weekDay;

        return $this;
    }

    /**
     * @return Collection|Appointment[]
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments[] = $appointment;
            $appointment->setSchedule($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getSchedule() === $this) {
                $appointment->setSchedule(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isFixed(): ?bool
    {
        return $this->fixed;
    }

    public function setFixed(?bool $fixed): self
    {
        $this->fixed = $fixed;

        return $this;
    }




}
