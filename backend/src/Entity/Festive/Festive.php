<?php

namespace App\Entity\Festive;

use App\Entity\User\User;
use App\Repository\FestiveRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;



#[ORM\Entity(repositoryClass: FestiveRepository::class)]
class Festive
{

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'festives')]
    #[ORM\JoinColumn(nullable:true, onDelete: 'CASCADE')]
    private ?User $user;

    // Campos

    #[ORM\Column(type: 'date', nullable: false)]
    private DateTime $date;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    public function __construct()
    {
        $this->date = UTCDateTime::setUTC(UTCDateTime::create());
        $this->user = null;
        $this->name = '';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = UTCDateTime::setUTC($date);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
