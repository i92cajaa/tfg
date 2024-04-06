<?php

namespace App\Entity\ExtraAppointmentField;

use App\Entity\Appointment\Appointment;
use App\Entity\User\User;
use App\Repository\ExtraAppointmentFieldRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;


#[ORM\Entity(repositoryClass: ExtraAppointmentFieldRepository::class)]
class ExtraAppointmentField
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: Appointment::class, inversedBy: 'extraAppointmentFields')]
    #[ORM\JoinColumn(nullable:true, onDelete: 'CASCADE')]
    private ?Appointment $appointment;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'extraAppointmentFields')]
    private User $user;

    // Campos

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $title;

    #[ORM\Column(type: 'string', length:255, nullable: false)]
    private string $type;

    #[ORM\Column(type: 'text', nullable: true)]
    private string $value;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $created_at;

    public function __construct()
    {
        $this->created_at = UTCDateTime::create();
    }

    public function getId(): ?string
    {
        return $this->id;
    }



    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    /**
     * @param mixed $appointment
     * @return ExtraAppointmentField
     */
    public function setAppointment($appointment): self
    {
        $this->appointment = $appointment;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return ExtraAppointmentField
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }


    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ExtraAppointmentField
     */
    public function setType(string $type): ExtraAppointmentField
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return ExtraAppointmentField
     */
    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     * @return ExtraAppointmentField
     */
    public function setCreatedAt(Datetime $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }




}
