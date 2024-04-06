<?php


namespace App\Entity\Appointment;


use App\Entity\User\User;
use App\Entity\Appointment\Appointment;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use App\Repository\AppointmentLogRepository;


#[ORM\Entity(repositoryClass: AppointmentLogRepository::class)]
class AppointmentLog
{
    const JOB_STATUS_CHANGED = 'Status Changed';
    const JOB_APPOINTMENT_CHANGED = 'Appointment Changed';
    const JOB_TIME_CHANGED = 'Hour Changed';
    const JOB_NEW_PAYMENT = 'New Payment';
    const JOB_ADD_TEMPLATE = 'New Template';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: Appointment::class, inversedBy: 'appointmentLogs')]
    #[ORM\JoinColumn(name: "appointment_id", referencedColumnName:"id", nullable:true, onDelete: 'CASCADE')]
    private Appointment $appointment;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'appointmentLogs')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:true, onDelete: 'SET NULL')]
    private ?User $whoChanged;

    // Campos

    #[ORM\Column(name:"job_done", type:"string", length:100, nullable:false)]
    private string $jobDone;

    #[ORM\Column(name:"created_at", type:"datetime", nullable:false)]
    private DateTime $createdAt;

    #[ORM\Column(name:"comments", type:"text", nullable:true)]
    private ?string $comments = null;


    public function __construct()
    {
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());
    }

    public function getId(): string
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
     * @return AppointmentLog
     */
    public function setAppointment(Appointment $appointment = null): AppointmentLog
    {
        if($appointment != null){
            $this->appointment = $appointment;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getJobDone(): string
    {
        return $this->jobDone;
    }

    /**
     * @param string $jobDone
     * @return AppointmentLog
     */
    public function setJobDone(string $jobDone): AppointmentLog
    {
        $this->jobDone = $jobDone;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return AppointmentLog
     */
    public function setCreatedAt(DateTime $createdAt): AppointmentLog
    {
        $this->createdAt = UTCDateTime::setUTC($createdAt);
        return $this;
    }

    /**
     * @return User|null
     */
    public function getWhoChanged(): ?User
    {
        return $this->whoChanged;
    }

    /**
     * @param User|null $whoChanged
     * @return AppointmentLog
     */
    public function setWhoChanged(?User $whoChanged): AppointmentLog
    {
        $this->whoChanged = $whoChanged;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string|null $comments
     * @return AppointmentLog
     */
    public function setComments(?string $comments): AppointmentLog
    {
        $this->comments = $comments;
        return $this;
    }


}
