<?php

namespace App\Entity\User;

use App\Entity\Center\Center;
use App\Entity\Document\Document;
use App\Entity\Notification\Notification;
use App\Entity\Role\Role;
use App\Entity\Schedule\Schedule;
use App\Repository\UserRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    // ----------------------------------------------------------------
    // Constants
    // ----------------------------------------------------------------

    const ENTITY = 'user';

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

    #[ORM\OneToOne(targetEntity:Document::class)]
    private ?Document $imgProfile = null;

    #[ORM\ManyToOne(targetEntity:Center::class, inversedBy: "users")]
    #[ORM\JoinColumn(name: "center_id", referencedColumnName:"id", nullable:true, onDelete: 'SET NULL')]
    private Center $center;

    #[ORM\OneToMany(mappedBy:"user", targetEntity: UserHasRole::class, cascade:["persist", "remove"])]
    private array|Collection $roles;

    #[ORM\OneToMany(mappedBy:"user", targetEntity: UserHasPermission::class, cascade:["persist"])]
    private array|Collection $permissions;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Notification::class)]
    private array|Collection $notifications;

    #[ORM\OneToMany(mappedBy:"user", targetEntity: UserHasDocument::class, cascade:["persist", "remove"])]
    private array|Collection $documents;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: UserHasLesson::class, cascade:["persist", "remove"])]
    private array|Collection $lessons;

    #[ORM\OneToMany(mappedBy: "teacher", targetEntity: Schedule::class, cascade:["persist", "remove"])]
    private array|Collection $schedules;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name: "name", type: "string", length: 255, unique: false, nullable:false)]
    private string $name;

    #[ORM\Column(name: "surnames", type: "string", length: 255, unique: false, nullable:false)]
    private string $surnames;

    #[ORM\Column(name: "email", type: "string", length: 180, unique: true, nullable:false)]
    private string $email;

    #[ORM\Column(name: "password", type: "string", unique: false, nullable:false)]
    private string $password;

    #[ORM\Column(name: "phone", type: "string", length: 255, unique: false, nullable:true)]
    private ?string $phone = null;

    #[ORM\Column(name: "created_at", type: "datetime", nullable:false)]
    private DateTime $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime", nullable:true)]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(name: "last_login", type: "datetime", nullable:true)]
    private ?DateTime $lastLogin = null;

    #[ORM\Column(name: "status", type: "boolean", nullable:false)]
    private bool $status;

    #[ORM\Column(name: "locale", type: "string", length: 255, unique: false, nullable:false)]
    private string $locale;

    #[ORM\Column(name: "menu_expanded", type: "boolean", length: 255, unique: false, nullable:false)]
    private bool $menuExpanded;

    #[ORM\Column(name: "calendar_interval", type: "string", length: 255, unique: false, nullable:true)]
    private ?string $calendarInterval = null;

    #[ORM\Column(name: "temporal_hash", type: "string", length: 255, unique: false, nullable:true)]
    private ?string $temporalHash = null;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->status = true;
        $this->locale = 'ES';
        $this->menuExpanded = false;
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());

        $this->roles = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->lessons = new ArrayCollection();
        $this->schedules = new ArrayCollection();
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
     * @return Document|null
     */
    public function getImgProfile(): ?Document
    {
        return $this->imgProfile;
    }

    /**
     * @return Center
     */
    public function getCenter(): Center
    {
        return $this->center;
    }

    /**
     * @return array|Collection
     */
    public function getRolesCollection(): array|Collection
    {
        return $this->roles;
    }

    /**
     * @return array|Collection
     */
    public function getPermissions(): array|Collection
    {
        return $this->permissions;
    }

    /**
     * @return array|Collection
     */
    public function getNotifications(): array|Collection
    {
        return $this->notifications;
    }

    /**
     * @return array|Collection
     */
    public function getDocuments(): array|Collection
    {
        return $this->notifications;
    }

    /**
     * @return array|Collection
     */
    public function getLessons(): array|Collection
    {
        return $this->lessons;
    }

    /**
     * @return array|Collection
     */
    public function getSchedules(): array|Collection
    {
        return $this->schedules;
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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
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
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return bool
     */
    public function isMenuExpanded(): bool
    {
        return $this->menuExpanded;
    }

    /**
     * @return string|null
     */
    public function getCalendarInterval(): ?string
    {
        return $this->calendarInterval;
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
     * @param Document|null $imgProfile
     * @return $this
     */
    public function setImgProfile(?Document $imgProfile): User
    {
        $this->imgProfile = $imgProfile;
        return $this;
    }

    /**
     * @param Center $center
     * @return $this
     */
    public function setCenter(Center $center): User
    {
        $this->center = $center;
        return $this;
    }

    /**
     * @param array|Collection $roles
     * @return $this
     */
    public function setRoles(array|Collection $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @param array|Collection $permissions
     * @return $this
     */
    public function setPermissions(array|Collection $permissions): User
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @param array|Collection $notifications
     * @return $this
     */
    public function setNotifications(array|Collection $notifications): User
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * @param array|Collection $documents
     * @return $this
     */
    public function setDocuments(array|Collection $documents): User
    {
        $this->documents = $documents;
        return $this;
    }

    /**
     * @param array|Collection $lessons
     * @return $this
     */
    public function setLessons(array|Collection $lessons): User
    {
        $this->lessons = $lessons;
        return $this;
    }

    /**
     * @param array|Collection $schedules
     * @return $this
     */
    public function setSchedules(array|Collection $schedules): User
    {
        $this->schedules = $schedules;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $surnames
     * @return $this
     */
    public function setSurnames(string $surnames): User
    {
        $this->surnames = $surnames;
        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string|null $phone
     * @return $this
     */
    public function setPhone(?string $phone): User
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): User
    {
        $this->createdAt = UTCDateTime::setUTC($createdAt);
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): User
    {
        $this->updatedAt = UTCDateTime::setUTC($updatedAt);
        return $this;
    }

    /**
     * @param \DateTimeInterface|null $lastLogin
     * @return $this
     */
    public function setLastLogin(?\DateTimeInterface $lastLogin): User
    {
        $this->lastLogin = UTCDateTime::setUTC($lastLogin);
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setStatus(bool $status): User
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): User
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param bool $menuExpanded
     * @return $this
     */
    public function setManuExpanded(bool $menuExpanded): User
    {
        $this->menuExpanded = $menuExpanded;
        return $this;
    }

    /**
     * @param string|null $calendarInterval
     * @return $this
     */
    public function setCalendarInterval(?string $calendarInterval): User
    {
        $this->calendarInterval = $calendarInterval;
        return $this;
    }

    /**
     * @param string|null $temporalHash
     * @return $this
     */
    public function setTemporalHash(?string $temporalHash): User
    {
        $this->temporalHash = $temporalHash;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD ROLE TO USER
     * ES: FUNCIÓN PARA AÑADIR ROL A USUARIO
     *
     * @param UserHasRole $userHasRole
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addRole(UserHasRole $userHasRole): User
    {
        if (!$this->roles->contains($userHasRole)) {
            $this->roles->add($userHasRole);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE ROLE FROM USER
     * ES: FUNCIÓN PARA BORRAR ROL DE USUARIO
     *
     * @param UserHasRole $userHasRole
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeRole(UserHasRole $userHasRole): User
    {
        if ($this->roles->contains($userHasRole)) {
            $this->roles->removeElement($userHasRole);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE ALL ROLES FROM USER
     * ES: FUNCIÓN PARA BORRAR TODOS LOS ROLES DE USUARIO
     *
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeAllRoles(): User
    {
        foreach ($this->roles as $role) {
            $this->roles->removeElement($role);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD PERMISSION TO USER
     * ES: FUNCIÓN PARA AÑADIR PERMISO A USUARIO
     *
     * @param UserHasPermission $userHasPermission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addPermission(UserHasPermission $userHasPermission): User
    {
        if (!$this->permissions->contains($userHasPermission)) {
            $this->permissions->add($userHasPermission);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE PERMISSION FROM USER
     * ES: FUNCIÓN PARA BORRAR PERMISO DE USUARIO
     *
     * @param UserHasPermission $userHasPermission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removePermission(UserHasPermission $userHasPermission): User
    {
        if ($this->permissions->contains($userHasPermission)) {
            $this->permissions->removeElement($userHasPermission);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE ALL PERMISSIONS FROM USER
     * ES: FUNCIÓN PARA BORRAR TODOS LOS PERMISOS DE USUARIO
     *
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeAllPermissions(): User
    {
        foreach ($this->permissions as $permission) {
            $this->permissions->removeElement($permission);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD NOTIFICATION TO USER
     * ES: FUNCIÓN PARA AÑADIR NOTIFICACIÓN A USUARIO
     *
     * @param Notification $notification
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addNotification(Notification $notification): User
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD DOCUMENT TO USER
     * ES: FUNCIÓN PARA AÑADIR DOCUMENTO A USUARIO
     *
     * @param UserHasDocument $userHasDocument
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addDocument(UserHasDocument $userHasDocument): User
    {
        if (!$this->documents->contains($userHasDocument)) {
            $this->documents->add($userHasDocument);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE DOCUMENT FROM USER
     * ES: FUNCIÓN PARA BORRAR DOCUMENTO DE USUARIO
     *
     * @param UserHasDocument $userHasDocument
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeDocument(UserHasDocument $userHasDocument): User
    {
        if ($this->documents->contains($userHasDocument)) {
            $this->documents->removeElement($userHasDocument);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE ALL DOCUMENTS FROM USER
     * ES: FUNCIÓN PARA BORRAR TODOS LOS DOCUMENTOS DE USUARIO
     *
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeAllDocuments(): User
    {
        foreach ($this->documents as $document) {
            $this->documents->removeElement($document);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD LESSON TO USER
     * ES: FUNCIÓN PARA AÑADIR CLASE A USUARIO
     *
     * @param UserHasLesson $userHasLesson
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addLesson(UserHasLesson $userHasLesson): User
    {
        if (!$this->lessons->contains($userHasLesson)) {
            $this->lessons->add($userHasLesson);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE LESSON FROM USER
     * ES: FUNCIÓN PARA BORRAR CLASE DE USUARIO
     *
     * @param UserHasLesson $userHasLesson
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeLesson(UserHasLesson $userHasLesson): User
    {
        if ($this->lessons->contains($userHasLesson)) {
            $this->lessons->removeElement($userHasLesson);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE ALL LESSONS FROM USER
     * ES: FUNCIÓN PARA BORRAR TODAS LAS CLASES DE USUARIO
     *
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeAllLessons(): User
    {
        foreach ($this->lessons as $lesson) {
            $this->lessons->removeElement($lesson);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD SCHEDULE TO USER
     * ES: FUNCIÓN PARA AÑADIR HORARIO A USUARIO
     *
     * @param Schedule $schedule
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addSchedule(Schedule $schedule): User
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE SCHEDULE FROM USER
     * ES: FUNCIÓN PARA BORRAR HORARIO DE USUARIO
     *
     * @param Schedule $schedule
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeSchedule(Schedule $schedule): User
    {
        if ($this->schedules->contains($schedule)) {
            $this->schedules->removeElement($schedule);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET ROLE ID'S
     * ES: FUNCIÓN PARA OBTENER LOS ID DE LOS ROLES DEL USUARIO
     *
     * @return array
     */
    // ----------------------------------------------------------------
    public function getRoleIds(): array
    {

        $arrayRoles = [];
        foreach($this->roles as $role){
            $arrayRoles[] = $role->getRole()->getId();
        }

        return $arrayRoles;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CHECK IF USER IS SUPER ADMIN
     * ES: FUNCIÓN PARA COMPROBAR SI EL USUARIO ES SUPER ADMIN
     *
     * @return bool
     */
    // ----------------------------------------------------------------
    public function isSuperAdmin(): bool
    {
        if(in_array(Role::ROLE_SUPERADMIN, $this->getRoleIds())) {
            return true;
        }

        return false;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CHECK IF USER IS ADMIN
     * ES: FUNCIÓN PARA COMPROBAR SI EL USUARIO ES ADMIN
     *
     * @return bool
     */
    // ----------------------------------------------------------------
    public function isAdmin(): bool
    {
        foreach ($this->roles as $role){
            if($role->getRole()->isAdmin()){
                return true;
            }
        }

        return false;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CHECK IF USER IS TEACHER
     * ES: FUNCIÓN PARA COMPROBAR SI EL USUARIO ES PROFESOR
     *
     * @return bool
     */
    // ----------------------------------------------------------------
    public function isTeacher(): bool
    {
        foreach ($this->roles as $role){
            if($role->getRole()->isTeacher()){
                return true;
            }
        }

        return false;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET HOW MANY UNSEEN NOTIFICATIONS THIS USER HAS
     * ES: FUNCIÓN PARA OBTENER EL NÚMERO DE NOTIFICACIONES SIN VER TIENE EL USUARIO
     *
     * @return int
     */
    // ----------------------------------------------------------------
    public function getUnSeenNotifications(): int
    {
        $count = 0;
        foreach ($this->notifications as $notification){
            if(!$notification->isSeen()){
                $count++;
            }
        }
        return $count;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    // UserInterface Methods
    // ----------------------------------------------------------------

    public function eraseCredentials(): void
    {
        //No se borran credenciales
    }

    public function getUserIdentifier(): string
    {
        return $this->getId();
    }

    public function getRoles(): array
    {
        $rolesArray = [];
        foreach ($this->roles as $role) {
            $rolesArray[] = $role->getRole()->getName();
        }

        return $rolesArray;
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
}
