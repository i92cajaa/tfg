<?php


namespace App\Entity\Task;

use App\Entity\Status\Status;
use App\Repository\TemplateLineTypeRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class TaskHasStatus
{

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'statuses')]
    #[ORM\JoinColumn(name: "task_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Task $task;


    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: "status_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Status $status;

    // Campos


    #[ORM\Column(type: 'date', nullable: false)]
    private DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create('now'));
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Task
     */
    public function getTask(): Task
    {
        return $this->task;
    }

    /**
     * @param Task $task
     * @return TaskHasStatus
     */
    public function setTask(Task $task): TaskHasStatus
    {
        $this->task = $task;
        return $this;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @param Status $status
     * @return TaskHasStatus
     */
    public function setStatus(Status $status): TaskHasStatus
    {
        $this->status = $status;
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
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }



}