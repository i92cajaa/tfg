<?php


namespace App\Entity\Task;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\Status\Status;
use App\Entity\Task\TaskHasStatus;
use App\Entity\User\User;
use App\Repository\TaskRepository;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Utils\Util;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;


#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    const ENTITY = 'task';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Appointment::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: "appointment_id", referencedColumnName:"id", nullable:true, onDelete: 'CASCADE')]
    private ?Appointment $appointment;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", nullable:true, onDelete: 'CASCADE')]
    private ?Client $client;


    #[ORM\ManyToOne(targetEntity: TaskHasStatus::class, inversedBy: 'task')]
    #[ORM\JoinColumn(name: "status_id", referencedColumnName:"id", nullable:true, onDelete: 'CASCADE')]
    private ?TaskHasStatus $currentStatus;

    // Campos

    #[ORM\Column(type:'string', length: 255, nullable: false)]
    private string $title;

    #[ORM\Column(type:'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type:'integer', nullable: true)]
    private ?int $timestamp;

    #[ORM\Column(type:'datetime', nullable: false)]
    private DateTime $creationDate;

    #[ORM\Column(type:'datetime', nullable: true)]
    private ?DateTime $estimatedStartDate;

    #[ORM\Column(type:'datetime', nullable: true)]
    private ?DateTime $endDate;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"task", targetEntity: TaskHasStatus::class, cascade:["persist", "remove"])]
    private Collection $statuses;

    public function __construct()
    {
        $this->creationDate = UTCDateTime::setUTC(UTCDateTime::create('now'));
        $this->statuses = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Task
     */
    public function setTitle(string $title): Task
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Task
     */
    public function setDescription(?string $description): Task
    {
        $this->description = $description;
        return $this;
    }


    /**
     * @return TaskHasStatus|null
     */
    public function getCurrentStatus(): ?TaskHasStatus
    {
        return $this->currentStatus;
    }

    /**
     * @param TaskHasStatus|null $currentStatus
     * @return Task
     */
    public function setCurrentStatus(?TaskHasStatus $currentStatus): Task
    {
        $this->currentStatus = $currentStatus;
        return $this;
    }

    /**
     * @return TaskHasStatus[]|Collection|null
     */
    public function getStatuses(): ArrayCollection|Collection|array|null
    {
        return $this->statuses;
    }

    /**
     * @return ?Appointment
     */
    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    /**
     * @param ?Appointment $appointment
     * @return Task
     */
    public function setAppointment(?Appointment $appointment): Task
    {
        $this->appointment = $appointment;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param ?Client $client
     * @return Task
     */
    public function setClient(?Client $client): Task
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param TaskHasStatus[]|Collection|null $statuses
     * @return Task
     */
    public function setStatuses($statuses)
    {
        $this->statuses = $statuses;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate(): DateTime
    {
        return $this->creationDate;
    }

    /**
     * @param DateTime $creationDate
     * @return Task
     */
    public function setCreationDate(DateTime $creationDate): Task
    {
        $this->creationDate = UTCDateTime::setUTC($creationDate);
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getEstimatedStartDate(): ?DateTime
    {
        return UTCDateTime::format($this->estimatedStartDate);
    }

    /**
     * @param DateTime|null $estimatedStartDate
     * @return Task
     */
    public function setEstimatedStartDate(?DateTime $estimatedStartDate): Task
    {
        $this->estimatedStartDate = UTCDateTime::setUTC($estimatedStartDate);
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate(): ?DateTime
    {
        return UTCDateTime::format($this->endDate);
    }

    /**
     * @param DateTime|null $endDate
     * @return Task
     */
    public function setEndDate(?DateTime $endDate): Task
    {
        $this->endDate = UTCDateTime::setUTC($endDate);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param string|null $timestamp
     * @return Task
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Task
     */
    public function setUser(User $user): Task
    {
        $this->user = $user;
        return $this;
    }

    public function addStatus(Status $status): Task
    {
        $complaintHasStatus = (new TaskHasStatus())
            ->setTask($this)
            ->setStatus($status);
        ;
        $this->statuses->add($complaintHasStatus);

        return $this;
    }

    /**
     * @return TaskHasStatus|null
     */
    public function getLastStatus(): ?TaskHasStatus
    {
        $complaintHasStatuses = $this->statuses->toArray();
        if(sizeof($complaintHasStatuses) > 0){
            usort($complaintHasStatuses, fn($a, $b) => $b->getCreatedAt()->getTimestamp() - $a->getCreatedAt()->getTimestamp());
            return $complaintHasStatuses[0];
        }
        return null;
    }

    public function getTimestampReadable(?TranslatorInterface $translator = null): string
    {
        if(!$this->timestamp || $this->timestamp == 0){
            $timestamp = 'Nothing';
        }else{
            $timestamp = Util::human_time_diff(0, $this->getTimestamp());
        }

        if($translator){
            $timestampText = '';
            foreach (explode(' ', $timestamp) as $timestampIndex){
                $timestampText .= $translator->trans($timestampIndex) . ' ';
            }
            $timestamp = $timestampText;

        }
        return $timestamp;
    }

    public function toArray(TranslatorInterface $translator): array
    {
        if($this->getAppointment()?->getTimeFrom()!=null){
            $appointmentTime = $this->getAppointment()?->getTimeFrom()->format('Y-m-d H:i');
        }else{
            $appointmentTime=null;
        }



        return [
            'id' => $this->getId(),
            'description' => $this->getDescription(),
            'title' => $this->getTitle(),
            'time' => $this->getCreationDate()->format('Y-m-d H:i'),
            'created_date' => $this->getCreationDate()->format('d-m-Y'),
            'estimated_start_date' => $this->getEstimatedStartDate()->format('d-m-Y'),
            'created_time' => $this->getCreationDate()->format('H:i'),
            'estimated_start_time' => $this->getEstimatedStartDate()->format('H:i'),
            'user' => $this->getUser()->getId(),
            'userFullName' => $this->getUser()->getName().' '.$this->getUser()->getSurnames(),
            'appointment' => $this->getAppointment()?->getId(),
            'appointmentDate' => $appointmentTime,
            'client' => $this->getClient()?->getId(),
            'clientFullName' => $this->getClient()?->getFullName(),
            'currentStatus' => $this->getCurrentStatus()? $translator->trans($this->getCurrentStatus()->getStatus()->getName()) : '',
            'timestamp' => $this->getTimestampReadable($translator),
        ];
    }

    public function toEvent(TranslatorInterface $translator): array {
        $statusName = $translator->trans($this->getCurrentStatus()->getStatus()->getName());
        if($this->getAppointment()?->getTimeFrom()!=null){
            $appointmentTime = $this->getAppointment()?->getTimeFrom()->format('Y-m-d H:i');
        }else{
            $appointmentTime=null;
        }
        return [
            'id'              => $this->getId(),
            'title'           => $this->getTitle(),
            'start'           => str_replace(' ', 'T', $this->getEstimatedStartDate()->format('Y-m-d H:i:s')),
            'end'             => $this->getEndDate() ? $this->getEndDate()->format('Y-m-d H:i:s') : null,
            'allDay'          => false,
            'resourceId'      => $this->getUser()?->getId(),
            'extendedProps'   => [
                'task_id'              => $this->getId(),
                'user'            => $this->getUser()->getId(),
                'status'          => $this->getCurrentStatus()->getStatus()->getId(),
                'statusName'      => $translator->trans($this->getCurrentStatus()->getStatus()->getName()),
                'timestamp'      => $this->getTimestampReadable($translator),
                'title'   => $this->getTitle(),
                'description'   => $this->getDescription(),
                'appointmentId'   => $this->getAppointment()?->getId(),
                'clientId'   => $this->getClient()?->getId(),
                'appointmentDate'   => $appointmentTime,
                'clientFullName'   => $this->getClient()?->getFullName(),
            ],
            'backgroundColor'   => '#022C38',
            'borderColor' => '#022C38',
        ];
    }

}