<?php

namespace App\Entity\Meeting;

use App\Entity\Appointment\Appointment;
use App\Entity\User\User;
use App\Repository\ExtraAppointmentFieldRepository;
use App\Repository\MeetingRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;


#[ORM\Entity(repositoryClass: MeetingRepository::class)]
class Meeting
{
    const ENTITY = 'meeting';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\OneToOne(inversedBy: "meeting", targetEntity: Appointment::class)]
    #[ORM\JoinColumn(name: "appointment_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private Appointment $appointment;

    // Campos

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private string $subject;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $joinUrl;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $joinWebUrl;

    #[ORM\Column(type: 'text', nullable: true)]
    private string $meetingCode;

    #[ORM\Column(type: 'array', nullable: true)]
    private array $options;


    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Appointment
     */
    public function getAppointment(): Appointment
    {
        return $this->appointment;
    }

    /**
     * @param Appointment $appointment
     * @return Meeting
     */
    public function setAppointment(Appointment $appointment): Meeting
    {
        $this->appointment = $appointment;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Meeting
     */
    public function setSubject(string $subject): Meeting
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getJoinUrl(): string
    {
        return $this->joinUrl;
    }

    /**
     * @param string $joinUrl
     * @return Meeting
     */
    public function setJoinUrl(string $joinUrl): Meeting
    {
        $this->joinUrl = $joinUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getJoinWebUrl(): string
    {
        return $this->joinWebUrl;
    }

    /**
     * @param string $joinWebUrl
     * @return Meeting
     */
    public function setJoinWebUrl(string $joinWebUrl): Meeting
    {
        $this->joinWebUrl = $joinWebUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getMeetingCode(): string
    {
        return $this->meetingCode;
    }

    /**
     * @param string $meetingCode
     * @return Meeting
     */
    public function setMeetingCode(string $meetingCode): Meeting
    {
        $this->meetingCode = $meetingCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return Meeting
     */
    public function setOptions(array $options): Meeting
    {
        $this->options = $options;
        return $this;
    }





}
