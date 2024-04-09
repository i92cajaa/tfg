<?php

namespace App\Entity\Client;

use App\Entity\Notification\Notification;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client implements PasswordAuthenticatedUserInterface
{

    // ----------------------------------------------------------------
    // Constants
    // ----------------------------------------------------------------

    const ENTITY = 'client';

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

    #[ORM\OneToMany(mappedBy:"client", targetEntity: Booking::class, cascade:["persist", "remove"])]
    private array|Collection $bookings;

    #[ORM\OneToMany(mappedBy:"client", targetEntity: Notification::class, cascade:["persist", "remove"])]
    private array|Collection $notifications;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name:"name", type:"string", length:255, unique: false, nullable:false)]
    private string $name;

    #[ORM\Column(name:"surnames", type:"string", length:255, unique: false, nullable:false)]
    private string $surnames;

    #[ORM\Column(name:"email", type:"string", length:255, unique: true, nullable:true)]
    private ?string $email = null;

    #[ORM\Column(name:"dni", type:"string", length:255, unique: true, nullable:false)]
    private string $dni;

    #[ORM\Column(name:"password", type:"string", length:255, unique: false, nullable:false)]
    private string $password;

    #[ORM\Column(name:"phone", type:"string", length:255, unique: false, nullable:false)]
    private string $phone;

    #[ORM\Column(name:"created_at", type:"datetime", unique: false, nullable:false)]
    private DateTime $createdAt;

    #[ORM\Column(name:"updated_at", type:"datetime", unique: false, nullable:true)]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(name:"last_login", type:"datetime", unique: false, nullable:true)]
    private ?DateTime $lastLogin = null;

    #[ORM\Column(name:"status", type:"boolean", unique: false, nullable:false)]
    private bool $status;

    #[ORM\Column(name:"temporal_hash", type:"string", length:255, unique: true, nullable:true)]
    private ?string $temporalHash = null;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());
        $this->status = true;
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
     * @return array|Collection
     */
    public function getBookings(): array|Collection
    {
        return $this->bookings;
    }

    /**
     * @return array|Collection
     */
    public function getNotifications(): array|Collection
    {
        return $this->notifications;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSurnames(): string
    {
        return $this->surnames;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->name . ' ' . $this->surnames;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getDni(): string
    {
        return $this->dni;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return UTCDateTime::format($this->createdAt);
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return UTCDateTime::format($this->updatedAt);
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastLogin(): ?\DateTimeInterface
    {
        return UTCDateTime::format($this->lastLogin);
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getTemporalHash(): ?string
    {
        return $this->temporalHash;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): Client
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param array|Collection $bookings
     * @return $this
     */
    public function setBookings(array|Collection $bookings): Client
    {
        $this->bookings = $bookings;
        return $this;
    }

    /**
     * @param array|Collection $notifications
     * @return $this
     */
    public function setNotifications(array|Collection $notifications): Client
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Client
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $surnames
     * @return $this
     */
    public function setSurnames(string $surnames): Client
    {
        $this->surnames = $surnames;
        return $this;
    }

    /**
     * @param string|null $email
     * @return $this
     */
    public function setEmail(?string $email): Client
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $dni
     * @return $this
     */
    public function setDni(string $dni): Client
    {
        $this->dni = $dni;
        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): Client
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone): Client
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): Client
    {
        $this->createdAt = UTCDateTime::setUTC($createdAt);
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): Client
    {
        $this->updatedAt = UTCDateTime::setUTC($updatedAt);
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $lastLogin
     * @return $this
     */
    public function setLastLogin(?\DateTimeInterface $lastLogin): Client
    {
        $this->lastLogin = UTCDateTime::setUTC($lastLogin);
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setStatus(bool $status): Client
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string $temporalHash
     * @return $this
     */
    public function setTemporalHash(string $temporalHash): Client
    {
        $this->temporalHash = $temporalHash;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD A BOOKING TO THE CLIENT
     * ES: FUNCIÓN PARA AÑADIR UNA RESERVA AL CLIENTE
     *
     * @param Booking $booking
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addBooking(Booking $booking): Client
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE A BOOKING FROM THE CLIENT
     * ES: FUNCIÓN PARA BORRAR UNA RESERVA DEL CLIENTE
     *
     * @param Booking $booking
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeBooking(Booking $booking): Client
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD A NOTIFICATION TO THE CLIENT
     * ES: FUNCIÓN PARA AÑADIR UNA NOTIFICACIÓN AL CLIENTE
     *
     * @param Notification $notification
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addNotification(Notification $notification): Client
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE A NOTIFICATION FROM THE CLIENT
     * ES: FUNCIÓN PARA BORRAR UNA NOTIFICACIÓN DEL CLIENTE
     *
     * @param Notification $notification
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeNotification(Notification $notification): Client
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
        }

        return $this;
    }
    // ----------------------------------------------------------------

}
