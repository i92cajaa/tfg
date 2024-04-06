<?php

namespace App\Entity\Invoice;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\User\User;
use App\Shared\Classes\UTCDateTime;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[UniqueEntity(fields:["invoiceNumber"], message:"Este número de factura ya existe")]
class Invoice
{
    const ENTITY = 'invoice';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: Appointment::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(name: "appointment_id", referencedColumnName:"id", nullable:true, onDelete:"SET NULL")]
    private ?Appointment $appointment;

    #[ORM\ManyToOne(targetEntity: User::class,  inversedBy: 'invoices')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:true, onDelete:"SET NULL")]
    private ?User $user;

    #[ORM\ManyToOne(targetEntity: Client::class,  inversedBy: 'invoices')]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", nullable:true, onDelete:"SET NULL")]
    private ?Client $client;

    // Campos

    #[ORM\Column(type: 'float', length: 255)]
    private float $amount;

    #[ORM\Column(type: 'float', length: 255)]
    private float $amountWithIva;

    #[ORM\Column(type: 'string', length: 255, unique: false)]
    private string $serie;

    #[ORM\Column(type: 'integer', length: 255)]
    private int $invoicePosition;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $invoiceNumber;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $dni;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $address;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $socialReason;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $billingAddress;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cif;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $companyPhone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $paymentMethod;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $entity;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $invoiceDate;

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $breakdown;


    public function __construct()
    {
        $this->createdAt = UTCDateTime::create();
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
     * @return Invoice
     */
    public function setAppointment(?Appointment $appointment): self
    {
        $this->appointment = $appointment;
        return $this;
    }

    public function getBreakdown(): ?array
    {
        return $this->breakdown;
    }

    public function setBreakdown(?array $breakdown): self
    {
        $this->breakdown = $breakdown;

        $total = 0;
        $totalWithIva = 0;
        foreach ($breakdown as $service){
            $totalWithIva += floatval($service['priceWithIva']);
            $total += floatval($service['priceWithoutIva']);
        }
        $this->setAmountWithIva($totalWithIva);
        $this->setAmount($total);

        return $this;
    }

    public function getIvaBreakdown(): array
    {
        $breakdown = [];
        foreach ($this->breakdown as $service)
        {
            $taxName = $service['iva'].'%';
            if(array_key_exists($taxName, $breakdown)){
                $breakdown[$taxName]['base'] += floatval($service['priceWithoutIva']);
                $breakdown[$taxName]['total'] += floatval($service['priceWithIva']);
            }else{
                $breakdown[$taxName]['base'] = floatval($service['priceWithoutIva']);
                $breakdown[$taxName]['total'] = floatval($service['priceWithIva']);
                $breakdown[$taxName]['percent'] = floatval($service['iva']);
            }

        }

        return $breakdown;
    }

    public function getInvoicePosition(): int
    {
        return $this->invoicePosition;
    }

    public function setInvoicePosition(int $invoicePosition):self
    {
        $this->invoicePosition = $invoicePosition;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEntity(): ?string
    {
        return $this->entity;
    }

    /**
     * @param string|null $entity
     * @return Invoice
     */
    public function setEntity(?string $entity): Invoice
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return string
     */
    public function getSerie(): string
    {
        return $this->serie;
    }

    /**
     * @param string $serie
     * @return Invoice
     */
    public function setSerie(string $serie): Invoice
    {
        $this->serie = $serie;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt):self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getInvoiceDate(): ?\DateTime
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\DateTime $invoiceDate):self
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(string $invoiceNumber):self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount):self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmountWithIva(): float
    {
        return $this->amountWithIva;
    }

    public function setAmountWithIva(float $amountWithIva):self
    {
        $this->amountWithIva = $amountWithIva;
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
     * @return Invoice
     */
    public function setUser(?User $user): Invoice
    {
        if($user){
            $this->setEntity(User::ENTITY);
        }

        $this->user = $user;
        return $this;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param Client|null $client
     * @return Invoice
     */
    public function setClient(?Client $client): Invoice
    {
        if($client){
            $this->setEntity(Client::ENTITY);
        }
        $this->client = $client;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Invoice
     */
    public function setName(string $name): Invoice
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDni(): ?string
    {
        return $this->dni;
    }

    /**
     * @param string|null $dni
     * @return Invoice
     */
    public function setDni(?string $dni): Invoice
    {
        $this->dni = $dni;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return Invoice
     */
    public function setPhone(?string $phone): Invoice
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return Invoice
     */
    public function setAddress(?string $address): Invoice
    {
        $this->address = $address;
        return $this;
    }



    public function getSocialReason(): ?string
    {
        return $this->socialReason;
    }

    public function setSocialReason(?string $socialReason):self
    {
        $this->socialReason = $socialReason;
        return $this;
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?string $billingAddress):self
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    public function getCif(): ?string
    {
        return $this->cif;
    }

    public function setCif(?string $cif):self
    {
        $this->cif = $cif;
        return $this;
    }

    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    public function setCompanyPhone(?string $companyPhone):self
    {
        $this->companyPhone = $companyPhone;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod):self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Invoice
     */
    public function setCreatedAt(\DateTime $createdAt): Invoice
    {
        $this->createdAt = $createdAt;
        return $this;
    }



}
