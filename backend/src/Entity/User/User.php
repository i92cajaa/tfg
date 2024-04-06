<?php

namespace App\Entity\User;

use App\Entity\Appointment\Appointment;
use App\Entity\Appointment\AppointmentLog;
use App\Entity\Area\Area;
use App\Entity\Client\Client;
use App\Entity\Center\Center;
use App\Entity\Client\ClientHasDocument;
use App\Entity\Document\Document;
use App\Entity\Festive\Festive;
use App\Entity\Invoice\Invoice;
use App\Entity\Notification\Notification;
use App\Entity\Role\Role;
use App\Entity\Schedules\Schedules;
use App\Entity\Service\Service;
use App\Entity\Task\Task;
use App\Entity\Template\Template;
use App\Entity\Template\TemplateType;
use App\Repository\UserRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    const ENTITY = 'user';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Relaciones

    #[ORM\OneToOne(targetEntity:Document::class)]
    private ?Document $imgProfile = null;

    #[ORM\ManyToOne(targetEntity:Center::class)]
    #[ORM\JoinColumn(name: "center_id", referencedColumnName:"id", nullable:true, onDelete: 'SET NULL')]
    private ?Center $center;

    // Campos

    #[ORM\Column(length: 180, unique: true, nullable:false)]
    private string $email;

    #[ORM\Column]
    private ?string $password;

    #[ORM\Column(length: 180)]
    private ?string $name;

    #[ORM\Column(length: 180, nullable:true)]
    private ?string $surnames;

    #[ORM\Column(length: 180,nullable: true) ]
    private ?string $phone;

    #[ORM\Column(length: 180, nullable:true)]
    private ?string $modality;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?DateTime $createdAt;

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $appointmentColor;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $updatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $lastLogin;

    #[ORM\Column(type: 'boolean')]
    private ?bool $status;

    #[ORM\Column(type: 'boolean')]
    private ?bool $darkMode;

    #[ORM\Column(type: 'boolean', options:["default" => "1"])]
    private ?bool $menuExpanded;

    #[ORM\Column(type: 'boolean', options:["default" => "0"])]
    private ?bool $vip;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $calendarInterval;

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $taskColor;

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $locale;

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $token;


    // Colecciones

    #[ORM\OneToMany(mappedBy:"user", targetEntity: UserHasRole::class, cascade:["persist", "remove"])]
    private Collection $roles;

    #[ORM\OneToMany(mappedBy:"user", targetEntity: UserHasArea::class, cascade:["persist", "remove"])]
    private Collection $areas;

    #[ORM\OneToMany(mappedBy:"user", targetEntity: UserHasClient::class, cascade:["persist"])]
    private Collection $clients;

    #[ORM\OneToMany(mappedBy:"user", targetEntity: UserHasPermission::class, cascade:["persist"])]
    private Collection $permissions;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Schedules::class)]
    private Collection $schedules;

    #[ORM\ManyToMany(targetEntity: Appointment::class, inversedBy: "users")]
    private Collection $appointments;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Festive::class)]
    private Collection $festives;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: UserHasService::class, cascade:["persist", "remove"])]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Template::class)]
    private Collection $templates;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: AppointmentLog::class)]
    private Collection $appointmentLogs;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Invoice::class)]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy:"user", targetEntity: UserHasDocument::class, cascade:["persist", "remove"])]
    private ?Collection $documents;

    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $document_adhesion;

    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $document_confidencial;
    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $document_image;
    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $document_deontological;
    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $document_anexo;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->calendarInterval = null;
        $this->email = '';
        $this->appointmentColor = '';
        $this->taskColor = '';
        $this->status = true;
        $this->vip = false;
        $this->darkMode = false;
        $this->menuExpanded = true;
        $this->locale = 'ES';
        $this->password = '';
        $this->name = null;
        $this->surnames = null;
        $this->modality = null;
        $this->phone = null;
        $this->token = null;

        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());
        $this->clients = new ArrayCollection();
        $this->roles             = new ArrayCollection();
        $this->permissions       = new ArrayCollection();
        $this->schedules       = new ArrayCollection();
        $this->appointments       = new ArrayCollection();
        $this->festives       = new ArrayCollection();
        $this->services       = new ArrayCollection();
        $this->areas       = new ArrayCollection();
        $this->templates       = new ArrayCollection();
        $this->appointmentLogs       = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->document_adhesion = null;
        $this->document_confidencial = null;
        $this->document_anexo = null;
        $this->document_deontological = null;
        $this->document_image = null;
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getId(): ?string
    {
        return $this->id;
    }


    public function getFullName()
    {
        return $this->name . ' ' .$this->surnames;
    }

    public function getFullNameMentor(){
        return $this->name . ' ' .$this->surnames.' - '.$this->getCenter()->getName();
    }

    public function isSuperAdmin(): bool
    {
        if(in_array(Role::ROLE_SUPERADMIN, $this->getRoleIds())) {
            return true;
        }

        return false;
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
     * @return User
     */
    public function setInvoices(ArrayCollection|Collection $invoices): User
    {
        $this->invoices = $invoices;
        return $this;
    }



    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->id;
    }

    /**
     * @return mixed
     */
    public function getCalendarInterval()
    {
        return $this->calendarInterval;
    }

    /**
     * @param mixed $calendarInterval
     * @return User
     */
    public function setCalendarInterval($calendarInterval)
    {
        $this->calendarInterval = $calendarInterval;
        return $this;
    }


    /**
     * @return ArrayCollection|Collection
     */
    public function getClients(): ArrayCollection|Collection
    {
        return $this->clients;
    }

    /**
     * @param ArrayCollection|Collection $clients
     * @return User
     */
    public function setClients(ArrayCollection|Collection $clients): User
    {
        $this->clients = $clients;
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
     * @return User
     */
    public function setLocale(?string $locale): User
    {
        $this->locale = $locale;
        return $this;
    }





      /**
     * Get roles
     *
     * @param bool $arrayMode
     * @return mixed
     */
    public function getRoleObjects()
    {
        $roles = [];
        foreach ($this->roles as $role){
            $roles[] = $role->getRole();
        }
        return $roles;
    }
    /**
     * Get roles
     *
     * @param bool $arrayMode
     * @return mixed
     */
    public function getRoles($arrayMode = true):array
    {
        if($arrayMode){
            $arrayRoles = [];
            $roles = $this->roles;
            foreach($roles as $rol){
                $arrayRoles[] = $rol->getRole()->getName();
            }

            return $arrayRoles;
        }
        return $this->roles->toArray();
    }
    /**
     * Get areas
     *
     * @param bool $arrayMode
     * @return mixed
     */
    public function getAreas($arrayMode = true):array
    {
        if($arrayMode){
            $arrayAreas = [];
            $areas = $this->areas;
            foreach($areas as $area){
                $arrayAreas[] = $area->getArea()->getName();
            }

            return $arrayAreas;
        }
        return $this->areas->toArray();
    }

    public function getRoleIds(): array
    {

        $arrayRoles = [];
        $roles = $this->roles;
        foreach($roles as $rol){
            $arrayRoles[] = $rol->getRole()->getId();
        }

        return $arrayRoles;
    }

    /**
     * Remove roles
     */
    public function removeAllRoles()
    {
        foreach ($this->roles as $role) {
            $this->roles->removeElement($role);
        }
        return true;
    }

    public function isAdmin(): bool
    {
        foreach ($this->getRoleObjects() as $role){
            if($role->isAdmin()){
                return true;
            }
        }

        return false;
    }

    public function getClient(){
        return $this->clients->last() ? $this->clients->last()->getClient() : null;
    }

    /**
     * Add role
     * @param Role $role
     * @return User
     */
    public function addRole(Role $role)
    {
        $userHasRole = (new UserHasRole())->setUser($this)->setRole($role);
        $this->roles->add($userHasRole);

        return $this;
    }

    /**
     * Add area
     * @param Area $area
     * @return User
     */
    public function addArea(Area $area)
    {
        $userHasArea = (new UserHasArea())->setUser($this)->setArea($area);
        $this->areas->add($userHasArea);

        return $this;
    }

    public function removeAllAreas() {
        foreach ($this->areas as $area) {
            $this->areas->removeElement($area);
        }
        return true;
    }

    /**
     * Add client
     * @param Client $area
     * @return User
     */
    public function addClient(Client $client)
    {
        $userHasClient = (new UserHasClient())->setUser($this)->setClient($client);
        $this->clients->add($userHasClient);

        return $this;
    }

    public function isProject(): bool
    {
        foreach ($this->getRoleObjects() as $role){
            if($role->isProject()){
                return true;
            }
        }

        return false;
    }

    public function isSandetel(): bool
    {
        foreach ($this->getRoleObjects() as $role){
            if($role->isSandetel()){
                return true;
            }
        }

        return false;
    }

    public function isDirector(): bool
    {
        foreach ($this->getRoleObjects() as $role){
            if($role->isDirector()){
                return true;
            }
        }

        return false;
    }

    public function isMentor(): bool
    {
        foreach ($this->getRoleObjects() as $role){
            if($role->isMentor()){
                return true;
            }
        }

        return false;
    }

    /**
     * Add role
     * @param $roles
     * @return User
     */
    public function addRoles($roles)
    {
        foreach($roles as $role) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * Remove role
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * @return ArrayCollection|Collection|array
     */
    public function getNotifications(): array
    {
        return $this->notifications->getValues();
    }

    /**
     * @return int
     */
    public function getUnSeenNotifications(): int
    {
        $notifications = $this->notifications->getValues();
        $count = 0;
        foreach ($notifications as $notification){
            if(!$notification->isSeen()){
                $count++;
            }
        }
        return $count;
    }

    /**
     * @param Notification[]|Collection $notifications
     */
    public function setNotifications(array $notifications): void
    {
        $this->notifications = $notifications;
    }

    public function addNotification(string $message): User
    {
        $notification = (new Notification())
            ->setUser($this)
            ->setMessage($$message)
        ;
        $this->notifications->add($notification);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getSurnames(): ?string
    {
        return $this->surnames;
    }

    public function setSurnames(string $surnames): self
    {
        $this->surnames = $surnames;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getImgProfile(): ?Document
    {
        return $this->imgProfile;
    }

    public function setImgProfile(?Document $img_profile): self
    {
        $this->imgProfile = $img_profile;

        return $this;
    }

    public function getAppointmentColor(): ?string
    {
        return $this->appointmentColor;
    }

    public function setAppointmentColor(?string $appointmentColor): self
    {
        $this->appointmentColor = $appointmentColor;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return UTCDateTime::format($this->createdAt);
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = UTCDateTime::setUTC($createdAt);

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return UTCDateTime::format($this->updatedAt);
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = UTCDateTime::setUTC($updatedAt);

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return UTCDateTime::format($this->lastLogin);
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = UTCDateTime::setUTC($lastLogin);

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }


    public function getDarkMode(): ?bool
    {
        return $this->darkMode;
    }

    public function setDarkMode(bool $darkMode): self
    {
        $this->darkMode = $darkMode;

        return $this;
    }

    public function getMenuExpanded(): ?bool
    {
        return $this->menuExpanded;
    }

    public function setMenuExpanded(bool $menuExpanded): self
    {
        $this->menuExpanded = $menuExpanded;

        return $this;
    }

    /**
     * @return Collection|Schedules[]
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function getSchedulesByWeekDay(?int $weekDay): array
    {
        $finalSchedule = [];

        /** @var Schedules $schedule */
        foreach ($this->schedules as $schedule) {
            if($schedule->getWeekDay() == $weekDay)
            {
                $finalSchedule[] = $schedule;
            }
        }

        usort($finalSchedule, function($a, $b) {return $a->getTimeFrom()->getTimestamp() - $b->getTimeFrom()->getTimestamp();});

        return $finalSchedule;
    }

    public function getSchedulesSchema(): array
    {
        $schema = [];
        /** @var Schedules $schedule */
        foreach ($this->schedules as $schedule) {
            $schema[$schedule->getWeekDay()][] = ['id' => $schedule->getId(), 'time_from' => $schedule->getTimeFrom(), 'time_to' => $schedule->getTimeTo()];
        }


        return $schema;
    }

    public function addSchedule(Schedules $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules[] = $schedule;
            $schedule->setUser($this);
        }

        return $this;
    }

    public function removeSchedule(Schedules $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getUser() === $this) {
                $schedule->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Appointment[]
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments[] = $appointment;
            $appointment->addUser($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getUsers().include($this->getId())) {
                $appointment->removeUser($this);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Festive[]
     */
    public function getFestives(): Collection
    {
        return $this->festives;
    }

    public function addFestive(Festive $festive): self
    {
        if (!$this->festives->contains($festive)) {
            $this->festives[] = $festive;
            $festive->setUser($this);
        }

        return $this;
    }

    public function removeFestive(Festive $festive): self
    {
        if ($this->festives->removeElement($festive)) {
            // set the owning side to null (unless already changed)
            if ($festive->getUser() === $this) {
                $festive->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Remove festives
     */
    public function removeAllFestives()
    {
        foreach ($this->festives as $festive) {
            $this->festives->removeElement($festive);
        }
        return true;
    }

    /**
     * @return Collection|Service[]
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function getServicesArray(): array
    {
        $services = [];
        /** @var UserHasService $service */
        foreach ($this->services as $service){
            $services[] = $service->getService();
        }

        return $services;
    }

    public function addService(Service $service): self
    {
        $userHasService = (new UserHasService())
            ->setService($service)
            ->setUser($this);

        if (!$this->services->contains($userHasService)) {
            $this->services->add($userHasService);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        /** @var UserHasService $userHasService */
        foreach ($this->services as $userHasService) {
            if($userHasService->getService() === $service){
                $this->services->removeElement($userHasService);
            }
        }

        return $this;
    }

    /**
     * Remove services
     */
    public function removeAllServices()
    {
        foreach ($this->services as $service) {
            $this->services->removeElement($service);
        }
        return true;
    }

    /**
     * @return false
     */
    public function getVip(): bool
    {
        return $this->vip;
    }

    /**
     * @param false $vip
     * @return User
     */
    public function setVip(bool $vip): User
    {
        $this->vip = $vip;
        return $this;
    }

    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getPermissionsArray(): array
    {
        $permissions = [];
        foreach ($this->getPermissions() as $userPermission) {
            $permission                       = $userPermission->getPermission();
            $group                            = $permission->getGroup();
            $permissions[$group->getName()][] = $permission->getAction();
        }

        return $permissions;
    }

    public function getPermissionObjects():array
    {
        $permissions = [];
        foreach ($this->getPermissions() as $userPermission) {
            $permission    = $userPermission->getPermission();
            $permissions[] = $permission;
        }

        return $permissions;
    }


    public function addPermission(UserHasPermission $userHasPermission): self
    {
        if (!in_array($userHasPermission->getPermission(), $this->getPermissionObjects()))
            $this->permissions->add($userHasPermission);

        return $this;
    }


    public function removePermission(UserHasPermission $permission): self
    {
        if ($this->permissions->contains($permission))
            $this->permissions->removeElement($permission);
        return $this;
    }

    public function removeAllPermissions(): User
    {
        foreach ($this->permissions as $permission) {
            $this->removePermission($permission);
        }
        return $this;
    }

    public function getScheduleSchema()
    {
        $schema = [];
        /** @var Schedules $schedule */
        foreach ($this->schedules as $schedule) {
            if($schedule->getStatus()){
                $schema[$schedule->getWeekDay()][] = [
                    'time_from'        => $schedule->getTimeFrom()->format('H:i'),
                    'time_to'        => $schedule->getTimeTo()->format('H:i'),
                ];
            }

        }

        return $schema;
    }

    public function getFestiveSchema()
    {
        return array_map(function ($festive){
            return $festive->getDate()->format('Y-m-d');
        }, $this->festives->toArray());

    }

    /**
     * @return ArrayCollection
     */
    public function getTemplates(): ArrayCollection
    {
        return $this->templates;
    }

    /**
     * @param ArrayCollection $templates
     * @return User
     */
    public function setTemplates(ArrayCollection $templates): User
    {
        $this->templates = $templates;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAppointmentLogs(): ArrayCollection
    {
        return $this->appointmentLogs;
    }

    /**
     * @param ArrayCollection $appointmentLogs
     * @return User
     */
    public function setAppointmentLogs(ArrayCollection $appointmentLogs): User
    {
        $this->appointmentLogs = $appointmentLogs;
        return $this;
    }

    /**
     * @return Task[]|Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param Task[]|Collection $tasks
     * @return User
     */
    public function setTasks($tasks): User
    {
        $this->tasks = $tasks;
        return $this;
    }

    /**
     * @return Task[]|Collection
     */
    public function getTasksByStatuses(array $statuses): array
    {
        $tasks = [];
        foreach ($this->tasks as $task){
            if(in_array($task->getCurrentStatus()->getStatus()->getId(), $statuses )){
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    /**
     * @return Task[]|Collection
     */
    public function getTasksByStatus(int $status): array
    {
        $tasks = [];
        foreach ($this->tasks as $task){
            if($task->getCurrentStatus()->getStatus()->getId() == $status ){
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    public function getFinishedTasks(): array
    {
        $tasks = $this->getTasks();
        $finishedTasks = [];
        foreach ($tasks as $task){
            if($task->getCurrentStatus()->getId() == Status::STATUS_TASK_COMPLETED ){
                $finishedTasks[] = $task;
            }
        }
        return $finishedTasks;
    }

    public function getAllTimestampOfTasks(): int
    {
        $timestamps = 0;
        foreach ($this->getTasks() as $task) {
            $timestamps += $task->getTimestamp();
        }
        return $timestamps;
    }

    /**
     * @return string|null
     */
    public function getTaskColor(): ?string
    {
        if($this->taskColor == null){
            return '#043C5C';
        }
        return $this->taskColor;
    }

    /**
     * @param mixed $taskColor
     * @return User
     */
    public function setTaskColor($taskColor)
    {
        $this->taskColor = $taskColor;
        return $this;
    }



    public function getAllTimestampReadable(): string
    {
        $timestamps = $this->getAllTimestampOfTasks();
        if($timestamps == 0){
            return "Nada";
        }
        return Util::human_time_diff(0, $timestamps);
    }

    public function getUserIdentifier(): string
    {
        return $this->getId();
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
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
    }


    /**
     * @return string|null
     */
    public function getModality(): ?string
    {
        return $this->modality;
    }

    /**
     * @param string|null $modality
     */
    public function setModality(?string $modality): void
    {
        $this->modality = $modality;
    }



    /**
     * @return Center|null
     */
    public function getCenter(): ?Center
    {
        return $this->center;
    }

    /**
     * @param Center|null $center
     */
    public function setCenter(?Center $center): void
    {
        $this->center = $center;
    }


    public function addDocument(Document $document): self
    {
        if ($this->documents == null){
            $this->documents = new ArrayCollection();
        }
        if (!in_array($document,$this->getDocuments())) {
            $userHasDocument = (new UserHasDocument());
            $userHasDocument->setDocument($document);
            $userHasDocument->setUser($this);
            if($this->documents != null){
                $this->documents->add($userHasDocument);
            }

        }

        return $this;
    }
    /**
     * @return array
     */
    public function getDocuments(): array
    {
        $documents = [];
        if($this->documents != null){
            foreach ($this->documents as $document)
            {
                $documents[] = $document->getDocument();
            }
        }
        return $documents;
    }

    /**
     * @param Collection|null $documents
     */
    public function setDocuments(?Collection $documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @return Document|null
     */
    public function getDocumentAdhesion(): ?Document
    {
        return $this->document_adhesion;
    }

    /**
     * @param Document|null $document_adhesion
     */
    public function setDocumentAdhesion(?Document $document_adhesion): void
    {
        $this->document_adhesion = $document_adhesion;
    }

    /**
     * @return Document|null
     */
    public function getDocumentConfidencial(): ?Document
    {
        return $this->document_confidencial;
    }

    /**
     * @param Document|null $document_confidencial
     */
    public function setDocumentConfidencial(?Document $document_confidencial): void
    {
        $this->document_confidencial = $document_confidencial;
    }

    public function getDocumentImage(): ?Document
    {
        return $this->document_image;
    }

    public function setDocumentImage(?Document $document_image): void
    {
        $this->document_image = $document_image;
    }

    public function getDocumentDeontological(): ?Document
    {
        return $this->document_deontological;
    }

    public function setDocumentDeontological(?Document $document_deontological): void
    {
        $this->document_deontological = $document_deontological;
    }

    public function getDocumentAnexo(): ?Document
    {
        return $this->document_anexo;
    }

    public function setDocumentAnexo(?Document $document_anexo): void
    {
        $this->document_anexo = $document_anexo;
    }

    //---------------------------------------------------------------------
    //Other Method
    //---------------------------------------------------------------------

    public function getTemplatesByTemplateType(TemplateType $templateType): array
    {
        $templates = [];
        /** @var Template $template */
        foreach ($this->templates as $template) {
            if($templateType === $template->getTemplateType()){
                $templates[] = $template;
            }
        }

        return array_reverse($templates);
    }






}
