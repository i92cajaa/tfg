<?php

namespace App\Entity\ClientRequest;

use App\Entity\Client\Client;
use App\Entity\Status\Status;
use App\Repository\ClientRequestRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRequestRepository::class)]
#[ORM\Table(name:'client_request')]
class ClientRequest
{
    const ENTITY = 'request';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'clientRequests')]
    #[ORM\JoinColumn(name: "status_id", referencedColumnName:"id", nullable:false)]
    private Status $status;

    // Campos

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $surnames;

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $phone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $comments;

    #[ORM\Column(type: 'array', nullable: true)]
    private ?array $availableTimeSlots;

    #[ORM\Column(type:"text", nullable: true)]
    private ?string $locale;

    #[ORM\Column(type:"text", nullable: true)]
    private ?string $timezone;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $createdAt;

    #[ORM\Column(type:"text", nullable: true)]
    private ?string $firstAnswer;

    #[ORM\Column(type:"text", nullable: true)]
    private ?string $secondAnswer;

    #[ORM\Column(type:"text", nullable: true)]
    private ?string $thirdAnswer;

    #[ORM\Column(type:"text", nullable: true)]
    private ?string $fourthAnswer;

    #[ORM\Column(type:"text", nullable: true)]
    private ?string $fifthAnswer;

    #[ORM\Column(type:"text", nullable: true)]
    private ?string $sixthAnswer;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripe_id;

    #[ORM\Column(type:"boolean", options:["default"=>"0"])]
    private bool $paid;

    public function __construct()
    {
        $this->name = '';
        $this->surnames = null;
        $this->email = '';
        $this->phone = null;
        $this->comments = null;
        $this->availableTimeSlots = [];
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());
    }

    public function getId(): string
    {
        return $this->id;
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

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string|null $locale
     * @return ClientRequest
     */
    public function setLocale(?string $locale): ClientRequest
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * @param string|null $timezone
     * @return ClientRequest
     */
    public function setTimezone(?string $timezone): ClientRequest
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = UTCDateTime::setUTC($createdAt);
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
     */
    public function setStatus(Status $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return ClientRequest
     */
    public function setPhone(?string $phone): ClientRequest
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurnames(): ?string
    {
        return $this->surnames;
    }

    /**
     * @param string $surnames
     */
    public function setSurnames(?string $surnames): self
    {
        $this->surnames = $surnames;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

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
     * @return ClientRequest
     */
    public function setComments(?string $comments): ClientRequest
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstAnswer(): ?string
    {
        return $this->firstAnswer;
    }

    /**
     * @param string|null $firstAnswer
     */
    public function setFirstAnswer(?string $firstAnswer): void
    {
        $this->firstAnswer = $firstAnswer;
    }

    /**
     * @return string|null
     */
    public function getSecondAnswer(): ?string
    {
        return $this->secondAnswer;
    }

    /**
     * @param string|null $secondAnswer
     */
    public function setSecondAnswer(?string $secondAnswer): void
    {
        $this->secondAnswer = $secondAnswer;
    }

    /**
     * @return string|null
     */
    public function getThirdAnswer(): ?string
    {
        return $this->thirdAnswer;
    }

    /**
     * @param string|null $thirdAnswer
     */
    public function setThirdAnswer(?string $thirdAnswer): void
    {
        $this->thirdAnswer = $thirdAnswer;
    }

    /**
     * @return string|null
     */
    public function getFourthAnswer(): ?string
    {
        return $this->fourthAnswer;
    }

    /**
     * @param string|null $fourthAnswer
     */
    public function setFourthAnswer(?string $fourthAnswer): void
    {
        $this->fourthAnswer = $fourthAnswer;
    }

    /**
     * @return string|null
     */
    public function getFifthAnswer(): ?string
    {
        return $this->fifthAnswer;
    }

    /**
     * @param string|null $fifthAnswer
     */
    public function setFifthAnswer(?string $fifthAnswer): void
    {
        $this->fifthAnswer = $fifthAnswer;
    }

    /**
     * @return string|null
     */
    public function getSixthAnswer(): ?string
    {
        return $this->sixthAnswer;
    }

    /**
     * @param string|null $sixthAnswer
     */
    public function setSixthAnswer(?string $sixthAnswer): void
    {
        $this->sixthAnswer = $sixthAnswer;
    }

    /**
     * @return string|null
     */
    public function getStripeId(): ?string
    {
        return $this->stripe_id;
    }

    /**
     * @param string|null $stripe_id
     */
    public function setStripeId(?string $stripe_id): void
    {
        $this->stripe_id = $stripe_id;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->paid;
    }

    /**
     * @param bool $paid
     */
    public function setPaid(bool $paid): void
    {
        $this->paid = $paid;
    }






    /**
     * @return array|null
     */
    public function getAvailableTimeSlots(): ?array
    {

        $total = [];

        foreach ($this->availableTimeSlots as $index => $timeSlot){

            $from =
                UTCDateTime::format(
                    UTCDateTime::create('H:i:s', $timeSlot['from']['time'].':00', new \DateTimeZone('UTC'))
                        ->setDate(1970,1, 4)
                        ->modify('+' . $timeSlot['from']['weekDay'] . ' days')
                )
            ;

            $to =
                UTCDateTime::format(
                    UTCDateTime::create('H:i:s', $timeSlot['to']['time'].':00', new \DateTimeZone('UTC'))
                        ->setDate(1970,1, 4)
                        ->modify('+' . $timeSlot['to']['weekDay'] . ' days')
                )
            ;

            $total[$index] = [
                'from' => [],
                'to' => []
            ];

            $total[$index]['from']['time'] = $from->format('H:i');
            $total[$index]['from']['weekDay'] = $from->format('w');
            $total[$index]['to']['time'] = $to->format('H:i');
            $total[$index]['to']['weekDay'] = $to->format('w');

            //dd($timeSlot, $total);
        }

        return $total;
    }

    /**
     * @param array|null $availableTimeSlots
     * @return Client
     */
    public function setAvailableTimeSlots(?array $availableTimeSlots): ClientRequest
    {
        $total = [];

        foreach ($availableTimeSlots as $index => $timeSlot){

            //$from = (new DateTime())->setTime(explode(':', $timeSlot['from']['time'])[0], explode(':',$timeSlot['from']['time'])[1]);
            $from = UTCDateTime::setUTC(
                UTCDateTime::create('H:i', $timeSlot['from']['time'])
            )
                ->setDate(1970,1, 4)
                ->modify('+' . $timeSlot['from']['weekDay'] . ' days')
            ;

            $to = UTCDateTime::setUTC(
                UTCDateTime::create('H:i', $timeSlot['to']['time'])
            )
                ->setDate(1970,1, 4)
                ->modify('+' . $timeSlot['to']['weekDay'] . ' days')
            ;

            $total[$index] = [
                'from' => [],
                'to' => []
            ];
            $total[$index]['from']['time'] = $from->format('H:i');
            $total[$index]['from']['weekDay'] = $from->format('w');
            $total[$index]['to']['time'] = $to->format('H:i');
            $total[$index]['to']['weekDay'] = $to->format('w');
        }

        $this->availableTimeSlots = $total;

        return $this;
    }

    public function formatClientData(): array {
        return [
            'name' => $this->name,
            'surnames' => $this->surnames,
            'email' => $this->email,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'availableTimeSlots' => $this->availableTimeSlots,
        ];
    }



    /*
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'name',
        ]));
    }
    */
}
