<?php

namespace App\Entity\Appointment;

use App\Entity\Area\Area;
use App\Entity\Center\Center;
use App\Entity\Client\Client;
use App\Entity\Document\Document;
use App\Entity\ExtraAppointmentField\ExtraAppointmentField;
use App\Entity\Invoice\Invoice;
use App\Entity\Meeting\Meeting;
use App\Entity\Notification\Notification;
use App\Entity\Payment\Payment;
use App\Entity\Schedules\Schedules;
use App\Entity\Service\Service;
use App\Entity\Status\Status;
use App\Entity\Task\Task;
use App\Entity\Template\Template;
use App\Entity\Template\TemplateType;
use App\Entity\User\User;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Interfaces\EntityWithExtraFields;
use App\Shared\Interfaces\EntityWithTemplates;
use DateTime;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use App\Repository\AppointmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment implements EntityWithExtraFields, EntityWithTemplates
{
    const ENTITY = 'appointment';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id;

    // Relaciones
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'appointments')]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'appointments')]
//    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", nullable: true, onDelete: 'CASCADE')]
    private Collection $client;

    #[ORM\ManyToOne(targetEntity: Schedules::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(name: "schedules_id", referencedColumnName:"id", nullable:true)]
    private ?Schedules $schedule;

    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(name: "status_id", referencedColumnName:"id", nullable:false)]
    private Status $statusType;

    #[ORM\OneToOne(mappedBy: 'meeting', targetEntity: Meeting::class)]
    #[ORM\JoinColumn(name: "meeting_id", referencedColumnName:"id", nullable:true , onDelete: 'CASCADE')]
    private ?Meeting $meeting;

    #[ORM\ManyToOne(targetEntity: Area::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(name: "area_id", referencedColumnName:"id", nullable:true)]
    private ?Area $area;

    #[ORM\ManyToOne(targetEntity:Center::class)]
    #[ORM\JoinColumn(name: "center_id", referencedColumnName:"id", nullable:true, onDelete: 'SET NULL')]
    private ?Center $center;

    #[ORM\OneToOne(mappedBy: 'report', targetEntity: Document::class)]
    #[ORM\JoinColumn(name: "report_id", referencedColumnName:"id", nullable:true)]
    private ?Document $report;

    #[ORM\OneToOne(mappedBy: 'reportMentor', targetEntity: Document::class)]
    #[ORM\JoinColumn(name: "report_mentor_id", referencedColumnName:"id", nullable:true)]
    private ?Document $reportSignMentor;

    #[ORM\OneToOne(mappedBy: 'reportProject', targetEntity: Document::class)]
    #[ORM\JoinColumn(name: "report_project_id", referencedColumnName:"id", nullable:true)]
    private ?Document $reportSignProject;

    #[ORM\OneToOne(mappedBy: 'photo', targetEntity: Document::class)]
    #[ORM\JoinColumn(name: "photo_id", referencedColumnName:"id", nullable:true)]
    private ?Document $photo;

    // Campos

    #[ORM\Column(type:"boolean")]
    private bool $status;

    #[ORM\Column(type:"datetime", nullable:true)]
    private ?DateTime $timeTo;

    #[ORM\Column(type:"datetime", nullable:true)]
    private ?DateTime $timeFrom;

    #[ORM\Column(type:"boolean", options:["default"=>"0"], nullable: true)]
    private ?bool $emailSent;

    #[ORM\Column(type:"float", nullable:true)]
    private ?float $totalPrice;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripe_id;

    #[ORM\Column(type:"boolean", options:["default"=>"0"])]
    private bool $paid;

    #[ORM\Column(type:"string", length:255, nullable:true)]
    private ?string $periodicId;

    #[ORM\Column(length: 180, nullable:true)]
    private ?string $modality;

    #[ORM\Column(type:"boolean", options:["default"=>"0"])]
    private bool $meetingAttached;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"appointment", targetEntity: Template::class)]
    private Collection $templates;

    #[ORM\OneToMany(mappedBy:"appointment", targetEntity: AppointmentLog::class, cascade:["persist", "remove"])]
    #[ORM\OrderBy(["createdAt" => "DESC"])]
    private Collection $appointmentLogs;

    #[ORM\OneToMany(mappedBy:"appointment", targetEntity: ExtraAppointmentField::class, cascade:["persist", "remove"], orphanRemoval:true)]
    private Collection $extraAppointmentFields;

    #[ORM\OneToMany(mappedBy:"appointment", targetEntity: AppointmentHasService::class, cascade:["persist", "remove"])]
    private Collection $services;

    #[ORM\OneToMany(mappedBy:"appointment", targetEntity: Payment::class, cascade:["persist", "remove"])]
    private Collection $payments;

    #[ORM\OneToMany(mappedBy:"appointment", targetEntity: Notification::class, cascade:["persist"])]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy:"appointment", targetEntity: Task::class, cascade:["persist"])]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy:"appointment", targetEntity: Invoice::class, cascade:["persist"])]
    #[ORM\OrderBy(["createdAt" => "DESC"])]
    private Collection $invoices;


    public function __construct()
    {
        $this->templates = new ArrayCollection();
        $this->extraAppointmentFields = new ArrayCollection();
        $this->services  = new ArrayCollection();
        $this->appointmentLogs  = new ArrayCollection();
        $this->payments  = new ArrayCollection();
        $this->notifications  = new ArrayCollection();
        $this->tasks  = new ArrayCollection();
        $this->invoices  = new ArrayCollection();
        $this->users  = new ArrayCollection();
        $this->client = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getId();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection|Collection|array
     */
    public function getNotifications(): ArrayCollection|Collection|array
    {
        return $this->notifications;
    }

    /**
     * @param Collection|Notification[] $notifications
     * @return Appointment
     */
    public function setNotifications(Collection|array $notifications): Appointment
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAppointmentLogs(): Collection
    {
        return $this->appointmentLogs;
    }

    /**
     * @param ArrayCollection $appointmentLogs
     */
    public function setAppointmentLogs(ArrayCollection $appointmentLogs): void
    {
        $this->appointmentLogs = $appointmentLogs;
    }

    public function getModality(): ?string
    {
        return $this->modality;
    }

    public function setModality(?string $modality): void
    {
        $this->modality = $modality;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): void
    {
        $this->area = $area;
    }





    public function getReport(): ?Document
    {
        return $this->report;
    }

    public function setReport(?Document $report): void
    {
        $this->report = $report;
    }

    public function getReportSignMentor(): ?Document
    {
        return $this->reportSignMentor;
    }

    public function setReportSignMentor(?Document $reportSignMentor): void
    {
        $this->reportSignMentor = $reportSignMentor;
    }

    public function getReportSignProject(): ?Document
    {
        return $this->reportSignProject;
    }

    public function setReportSignProject(?Document $reportSignProject): void
    {
        $this->reportSignProject = $reportSignProject;
    }

    public function getPhoto(): ?Document
    {
        return $this->photo;
    }

    public function setPhoto(?Document $photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @return Meeting|null
     */
    public function getMeeting(): ?Meeting
    {
        return $this->meeting;
    }

    /**
     * @param Meeting|null $meeting
     * @return Appointment
     */
    public function setMeeting(?Meeting $meeting): Appointment
    {
        $this->meeting = $meeting;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMeetingAttached(): bool
    {
        return $this->meetingAttached;
    }

    /**
     * @param bool $meetingAttached
     * @return Appointment
     */
    public function setMeetingAttached(bool $meetingAttached): Appointment
    {
        $this->meetingAttached = $meetingAttached;
        return $this;
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
     * @return array
     */
    public function getPayments(): array
    {
        return $this->payments->toArray();
    }

    /**
     * @param ArrayCollection $payments
     * @return Appointment
     */
    public function setPayments(ArrayCollection $payments): Appointment
    {
        $this->payments = $payments;
        return $this;
    }

    public function getPaymentsAmountForServices(): array
    {
        $result = [];

        /** @var Payment $payment */
        foreach ($this->payments as $payment) {
            if(!@$result[$payment->getService()]){
                $result[$payment->getService()] = 0;
            }

            $result[$payment->getService()] += $payment->getAmount();
        }

        return $result;
    }

    public function checkPaid():bool
    {
        $payments = $this->getPaymentsAmountForServices();

        /** @var AppointmentHasService $service */
        foreach ($this->services as $service){
            $total = $service->getTotalPrice(true);

            $remaining = $total - floatval(@$payments[$service->getService()->getName()]);

            if($remaining > 0){
                return false;
            }
        }
        return true;

    }

    public function getServicesFormatForPayments():array
    {
        $result = [];

        $payments = $this->getPaymentsAmountForServices();

        /** @var AppointmentHasService $service */
        foreach ($this->services as $service){
            $total = $service->getTotalPrice(true);

            $remaining = $total - floatval(@$payments[$service->getService()->getName()]);

            $result[] = [
                'name' => $service->getService()->getName(),
                'total' => $total,
                'remaining' => $remaining,
            ];
        }
        return $result;

    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getInvoices(): ArrayCollection|Collection
    {
        return $this->invoices;
    }

    /**
     * @param ArrayCollection|Collection $invoices
     * @return Appointment
     */
    public function setInvoices(ArrayCollection|Collection $invoices): Appointment
    {
        $this->invoices = $invoices;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->getUsers()[0];
    }

    /*public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }*/



    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTimeTo(?bool $nonUTCFormat = true): ?\DateTime
    {
        return $this->timeTo != null && $nonUTCFormat ? UTCDateTime::format($this->timeTo) : $this->timeTo;
    }

    public function setTimeTo(\DateTime $timeTo): self
    {
        $this->timeTo = UTCDateTime::setUTC($timeTo);

        return $this;
    }

    public function getTimeFrom(?bool $nonUTCFormat = true): ?\DateTime
    {
        
        return $this->timeFrom != null && $nonUTCFormat ? UTCDateTime::format($this->timeFrom) : $this->timeFrom;
    }

    public function setTimeFrom(\DateTime $timeFrom): self
    {
        $this->timeFrom = UTCDateTime::setUTC($timeFrom);

        return $this;
    }

    public function getSchedule(): ?Schedules
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedules $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function addTemplate(Template $template): self
    {
        if (!$this->templates->contains($template)) {
            $this->templates[] = $template;
            $template->setAppointment($this);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getExtraAppointmentFields(): Collection
    {
        return $this->extraAppointmentFields;
    }

    public function addExtraAppointmentField(ExtraAppointmentField $extraAppointmentField): self
    {
        if (!$this->extraAppointmentFields->contains($extraAppointmentField)) {
            $this->extraAppointmentFields[] = $extraAppointmentField;
            $extraAppointmentField->setAppointment($this);
        }

        return $this;
    }

    public function extraFieldValueByTitle(string $name): string
    {
        foreach ($this->extraAppointmentFields as $extraAppointmentField) {
            if ($extraAppointmentField->getTitle() === $name) {
                return $extraAppointmentField->getValue();
            }
        }

        return '';
    }

    public function removeAllAppointmentExtraFields(): Appointment
    {
        foreach ($this->extraAppointmentFields as $extraAppointmentField) {
            $this->extraAppointmentFields->removeElement($extraAppointmentField);
        }
        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        if ($this->templates->removeElement($template)) {
            // set the owning side to null (unless already changed)
            if ($template->getAppointment() === $this) {
                $template->setAppointment(null);
            }
        }

        return $this;
    }

    public function removeAllTemplates(): self
    {


        $this->templates->clear();

        return $this;
    }

    public function getEmailSent(): ?bool
    {
        return $this->emailSent;
    }

    public function setEmailSent(bool $emailSent): self
    {
        $this->emailSent = $emailSent;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getServiceBreakdown():array
    {
        $breakdown = [];
        /** @var AppointmentHasService $service */
        foreach ($this->services as $service){
            $breakdown[] = [
                'service' => $service->getService()->getName(),
                'priceWithoutIva' => $service->getTotalPrice(false) ? $service->getTotalPrice(false) : $service->getService()->getTotalPrice(false),
                'priceWithIva' => $service->getTotalPrice(true) ? $service->getTotalPrice(true) : $service->getService()->getTotalPrice(true),
                'iva' => $service->getIva() ?: $service->getService()->getIva()

            ];
        }

        return $breakdown;
    }

    public function getServiceUserBreakdown():array
    {
        $breakdown = [];
        /** @var AppointmentHasService $service */
        foreach ($this->services as $service){
            $breakdown[$service->getService()->getName()] = [
                'service' => $service->getService()->getName(),
                'priceWithoutIva' => $service->getTotalPrice(false) ?
                    ($service->getTotalPrice(false)* $this->getUser()->getAppointmentPercentage())/100 :
                    ($service->getService()->getTotalPrice(false) * $this->getUser()->getAppointmentPercentage())/100,
                'priceWithIva' => $service->getTotalPrice(true) ?
                    ($service->getTotalPrice(true) * $this->getUser()->getAppointmentPercentage())/100 :
                    ($service->getService()->getTotalPrice(true) * $this->getUser()->getAppointmentPercentage())/100,
                'iva' => $service->getIva() ?: $service->getService()->getIva()

            ];
        }

        return $breakdown;
    }

    public function getTotalWithIva():float
    {
        $total = 0;
        foreach ($this->services as $service){
            $price =  $service->getTotalPrice(true)?$service->getTotalPrice(true):$service->getService()->getTotalPrice(true);

            $total += $price;

        }
        return $total;
    }

    public function getTotalWithoutIva():float
    {
        $total = 0;
        foreach ($this->services as $service){
            $price =  $service->getTotalPrice(false)?$service->getTotalPrice(false):$service->getService()->getTotalPrice(false);

            $total += $price;

        }
        return $total;
    }

    /**
     * @return array
     */
    public function getServices(): array
    {
        $services = [];

        foreach ($this->services as $service)
        {
            $services[] = $service->getService();
        }

        return $services;
    }

    public function getService()
    {
        $services = null;

        foreach ($this->services as $service)
        {
            $services = $service->getService();
        }

        return $services;
    }


    public function addService(Service $service): self
    {
        if (!in_array($service,$this->getServices())) {
            $appointmentHasService = (new AppointmentHasService())
                ->setAppointment($this)
                ->setService($service)
                ->setPrice($service->getPrice())
                ->setIva($service->getIva())
                ->setIvaApplied($service->getIvaApplied())
            ;

            $this->services->add($appointmentHasService);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        foreach ($this->services as $appointmentHasService){
            if ($appointmentHasService->getService() == $service) {
                $this->services->removeElement($appointmentHasService);
            }
        }

        return $this;
    }

    public function removeAllService(): self
    {
        foreach ($this->services as $appointmentHasService){
            $this->services->removeElement($appointmentHasService);

        }


        return $this;
    }

    public function getServiceMinutes(): int
    {
        $minutes = 0;
        /** @var AppointmentHasService $service */
        foreach ($this->services as $service){
            $minutes += $service->getService()->getNeededTime();
        }

        return $minutes;
    }

    public function getServiceHours(): float
    {
        $minutes = $this->getServiceMinutes();
        $hours = $minutes / 60.0;

        return round($hours, 2); // Redondea a 2 decimales
    }

    public function calculateTotalPrice()
    {
        $total = 0;
        foreach ($this->services as $service)
        {
            $total += $service->getPrice()?$service->getPrice():$service->getService()->getPrice();
        }

        $this->setTotalPrice($total);
    }

    public function getAppointmentHasServices(): ArrayCollection|Collection
    {
        return $this->services;
    }

    public function getStatusType(): ?Status
    {
        return $this->statusType;
    }

    public function setStatusType(?Status $statusType): self
    {
        $this->statusType = $statusType;

        if($statusType->getId() == Status::STATUS_CANCELED_DIRECTOR || $statusType->getId() == Status::STATUS_CANCELED_MENTOR){
            $this->setStatus(false);
        }else{
            $this->setStatus(true);
        }

        return $this;
    }

    public function getCenter(): ?Center
    {
        if($this->center == null){
            if ($this->getUser()!=null){
                return $this->getUser()->getCenter();
            }elseif ($this->getClient()!=null){
                return $this->getClient()->getCenter();
            }
        }
        return $this->center;
    }

    public function setCenter(?Center $center): void
    {
        $this->center = $center;
    }



    public function getPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(?bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

    public function getArrayServicesIds(): array
    {
        $serviceIds = [];
        foreach ($this->getServices() as $service) {
            $serviceIds[] = $service->getId();
        }
        return $serviceIds;
    }

    public function getPeriodicId(): ?string
    {
        return $this->periodicId;
    }

    public function setPeriodicId(?string $periodicId): self
    {
        $this->periodicId = $periodicId;

        return $this;
    }

    /**
     * @return ArrayCollection|Collection|array
     */
    public function getTasks(): ArrayCollection|Collection|array
    {
        return $this->tasks;
    }

    /**
     * @param Collection|Task[] $tasks
     * @return Appointment
     */
    public function setTasks(Collection|array $tasks): self
    {
        $this->tasks = $tasks;
        return $this;
    }

    public function getTasksByStatus(int $status, User $user): array
    {
        $tasks = [];

        $admin = $user->isAdmin();

        foreach($this->tasks as $task){
            if(
                $task->getCurrentStatus()->getStatus()->getId() == $status &&
                ((!$admin && $task->getUser()->getId() == $user->getId()) || $admin)

            ){
                $tasks[] = $task;
            }
        }

        return $tasks;

    }

    public function getRemainingPrice(): ?float
    {
        $remaining = $this->getTotalPrice();

        /** @var Payment $payment */
        foreach ($this->payments as $payment){
            $remaining -= $payment->getAmount();
        }

        return $remaining;
    }

    public function getPaymentMethods(): string
    {
        $paymentMethods = [];

        /** @var Payment $payment */
        foreach ($this->payments as $payment){
            $paymentMethods[] = $payment->getPaymentMethod();
        }

        $paymentMethods = array_unique($paymentMethods);

        return implode(', ', $paymentMethods);
    }

    public function getAppointmentData(): array
    {
        return [
            'client' => $this->getClient()->getId(),
            'services' => $this->getArrayServicesIds(),
            'schedules' => $this->getSchedule()->getId(),
            'time_from' => $this->getTimeFrom(false)->format('H:i'),
            'time_to' => $this->getTimeTo(false)->format('H:i'),
            'appointmentDate' => $this->getTimeTo(false)->format('Y-m-d')
        ];
    }

    public function toEvent(?bool $showUser = false): array
    {
        $clientId = null;
        if($this->getService()==null){
            return [];
        }

        if($this->getClient() != null && $this->getUser() != null && !$this->getService()->isForAdmin() && !$this->getService()->isForClient()){
            $title = $showUser ? $this->getUser()->getFullName() : $this->getClient()->getName()." - ".$this->getUser()->getFullName()." - ".$this->getCenter()->getName();
            $clientId =  $this->getClient()->getId();
            $borderColor =  $this->getCenter()->getColor();
        }else{
            if($this->getClient() != null && !$this->getService()->isForClient()){
                $title = $this->getService()->getName().' - '.$this->getClient()->getName().' - '.$this->getCenter()->getName();
            }else{
                $title = $this->getService()->getName().' - '.$this->getCenter()->getName();//
            }

            $borderColor =  $this->getCenter()->getColor();
        }


        return [
            'id'              => $this->getId(),
            'title'           => $title,
            'start'           => $this->getTimeFrom()->format('Y-m-d H:i:s'),
            'end'             => $this->getTimeTo()->format('Y-m-d H:i:s'),
            'allDay'          => false,
            'display'         => 'block',
            'resourceId'      => $this->getUser() ? $this->getUser()->getId() : null,
            'extendedProps'   => [
                'id'              => $this->getId(),
                'user'            => $this->getUser() ? $this->getUser()->getId() : null,
                'client'         => $clientId,
                'status'          => $this->getStatus() ? 1 : 0,
                'date'            => $this->getTimeFrom()->format('Y-m-d'),
                'timeFrom'       => $this->getTimeFrom()->format('H:i'),
                'timeTo'         => $this->getTimeTo()->format('H:i'),
                'scheduleId'      => $this->getSchedule()->getId(),
                'services'        => $this->getArrayServicesIds(),
                'calendar'        => $this->getStatus() ? 'Activa' : 'Liberada',
                'borderColor' => $borderColor,
                'backgroundColor' => $borderColor
            ],
            'backgroundColor' => '022C38',
        ];
    }

    public function getTemplatesByTemplateType(TemplateType $templateType): array
    {
        $templates = [];
        /** @var Template $template */
        foreach ($this->templates as $template) {
            if($templateType === $template->getTemplateType()){
                $templates[] = $template;
            }
        }

        return $templates;
    }

    public function getUserInvoice() :?Invoice
    {
        foreach ($this->invoices as $invoice){
            if($invoice->getUser()){
                return $invoice;
            }
        }

        return null;
    }

    public function getClientInvoice() :?Invoice
    {
        foreach ($this->invoices as $invoice){
            if($invoice->getClient()){
                return $invoice;
            }
        }

        return null;
    }


    public function __clone() {
        $this->id = null;
        $this->timeFrom = $this->getTimeFrom(false);
        $this->timeTo = $this->getTimeTo(false);

        $servicesClone = new ArrayCollection();
        /** @var AppointmentHasService $item */
        foreach ($this->services as $item) {
            $itemClone = clone $item;
            $itemClone->setAppointment($this);
            $servicesClone->add($itemClone);
        }
        $this->services = $servicesClone;

        $eafClone = new ArrayCollection();
        /** @var AppointmentHasService $item */
        foreach ($this->extraAppointmentFields as $item) {
            $itemClone = clone $item;
            $itemClone->setAppointment($this);
            $eafClone->add($itemClone);
        }
        $this->extraAppointmentFields = $eafClone;

    }

    public function getClient(): ?Client
    {
        return $this->getClients()[0];
    }


    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function removeAllUsers(): self
    {
        $this->users->clear();

        return $this;
    }

    /**
     * @return Collection
     */
    public function getClients(): Collection
    {
        return $this->client;
    }

    public function addClient(Client $client): self
    {
        if (!$this->client->contains($client)) {
            $this->client[] = $client;
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        $this->client->removeElement($client);

        return $this;
    }

}
