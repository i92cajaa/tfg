<?php
namespace App\Entity\Notification;


use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\User\User;
use App\Repository\NotificationRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;


#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    const FULL_CLASS = 'full_class';
    const NEW_CLASS_PETITION = 'new_class_petition';

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private $id;

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    #[ORM\ManyToOne(targetEntity:User::class, inversedBy: "notifications")]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:true, onDelete:"CASCADE")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity:Client::class, inversedBy: "notifications")]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", nullable:true, onDelete:"CASCADE")]
    private ?Client $client = null;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(type: 'text', nullable: false)]
    private string $message;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $type;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $link = null;

    #[ORM\Column(type: 'boolean', nullable: false, options:["default"=> 0])]
    private bool $seen;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->createdAt         = UTCDateTime::create();
        $this->seen              = false;
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
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isSeen(): bool
    {
        return $this->seen;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

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
     * @param User|null $user
     * @return Notification
     */
    public function setUser(?User $user): Notification
    {
        $this->user = $user;
        return $this;
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