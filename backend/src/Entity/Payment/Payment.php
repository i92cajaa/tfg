<?php

namespace App\Entity\Payment;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Shared\Classes\UTCDateTime;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    const ENTITY = 'payment';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity:Client::class, inversedBy: "payments")]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", nullable:true, onDelete:"SET NULL")]
    private ?Client $client;

    #[ORM\ManyToOne(targetEntity:Appointment::class, inversedBy: "payments")]
    #[ORM\JoinColumn(name: "appointment_id", referencedColumnName:"id", nullable:false, onDelete:"CASCADE")]
    private Appointment $appointment;

    // Campos

    #[ORM\Column(type: 'float', length: 255)]
    private float $amount;

    #[ORM\Column(type: 'string', length: 255)]
    private string $paymentMethod;

    #[ORM\Column(type: 'string', length: 255)]
    private string $service;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $paymentDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $createdAt;



    public function __construct()
    {
        $this->createdAt = UTCDateTime::create();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function setService(string $service): Payment
    {
        $this->service = $service;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): Payment
    {
        $this->amount = $amount;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): Payment
    {
        $this->client = $client;
        return $this;
    }

    public function getPaymentMethod():string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): Payment
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getPaymentDate(): ?\DateTime
    {

        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTime $paymentDate):self
    {
        $this->paymentDate = UTCDateTime::setUTC($paymentDate);

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): Payment
    {
        $this->createdAt = UTCDateTime::setUTC($createdAt);
        return $this;
    }

    public function getAppointment():?Appointment
    {
        return $this->appointment;
    }

    public function setAppointment(Appointment $appointment): Payment
    {
        $this->appointment = $appointment;
        return $this;
    }


}
