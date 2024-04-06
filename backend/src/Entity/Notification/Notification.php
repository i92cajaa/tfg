<?php
namespace App\Entity\Notification;


use App\Entity\Appointment\Appointment;
use App\Entity\User\User;
use App\Repository\NotificationRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;


#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity:User::class, inversedBy: "notifications")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:true, onDelete:"CASCADE")]
    private ?User $user;

    // Campos

    #[ORM\Column(type: 'text', nullable: false)]
    private string $message;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $link;

    #[ORM\Column(type: 'boolean', nullable: false, options:["default"=> 0])]
    private bool $seen;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt         = UTCDateTime::create();
        $this->seen              = false;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Notification
     */
    public function setMessage(string $message): Notification
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return Notification
     */
    public function setUser(?User $user): Notification
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSeen(): bool
    {
        return $this->seen;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        if($this->link == null){
            return '#';
        }
        return $this->link;
    }

    /**
     * @param string|null $link
     * @return Notification
     */
    public function setLink(?string $link): Notification
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @param bool $seen
     * @return Notification
     */
    public function setSeen(bool $seen): Notification
    {
        $this->seen = $seen;
        return $this;
    }



}