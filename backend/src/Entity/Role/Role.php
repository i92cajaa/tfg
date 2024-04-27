<?php


namespace App\Entity\Role;


use App\Entity\User\UserHasRole;
use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{

    // ----------------------------------------------------------------
    // Constants
    // ----------------------------------------------------------------

    const ROLE_SUPERADMIN = 1;

    const ROLE_ADMIN = 2;

    const ROLE_TEACHER = 3;

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    #[ORM\OneToMany(mappedBy:"role", targetEntity: UserHasRole::class, cascade:["persist"])]
    private array|Collection $users;

    #[ORM\OneToMany(mappedBy:"role", targetEntity: RoleHasPermission::class, cascade:["persist"])]
    private array|Collection $permissions;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(length: 180, nullable: false)]
    public string $name;

    #[ORM\Column(length: 180, nullable: false)]
    public string $color = '#000000';

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $admin;

    #[ORM\Column(length: 180, nullable: true)]
    public ?string $description = null;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->users       = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array|Collection
     */
    public function getUserHasRoles(): array|Collection
    {
        return $this->users;
    }

    /**
     * @return array|Collection
     */
    public function getPermissions(): array|Collection
    {
        return $this->permissions;
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
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return bool|null
     */
    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param array|Collection $users
     * @return Role
     */
    public function setUsers(array|Collection $users): Role
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @param string $name
     * @return Role
     */
    public function setName(string $name): Role
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $color
     * @return Role
     */
    public function setColor(string $color): Role
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @param bool|null $admin
     * @return $this
     */
    public function setAdmin(?bool $admin): Role
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @param string|null $description
     * @return Role
     */
    public function setDescription(?string $description): Role
    {
        $this->description = $description;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD USER TO ROLE
     * ES: FUNCIÓN PARA AÑADIR USUARIO A ROL
     *
     * @param UserHasRole $userHasRole
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addUser(UserHasRole $userHasRole): Role
    {
        if(!$this->users->contains($userHasRole)) {
            $userHasRole->setRole($this);
            $this->users->add($userHasRole);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE USER TO ROLE
     * ES: FUNCIÓN PARA BORRAR USUARIO A ROL
     *
     * @param UserHasRole $userHasRole
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeUser(UserHasRole $userHasRole): Role
    {
        if($this->users->contains($userHasRole)) {
            $this->users->removeElement($userHasRole);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD PERMISSION TO ROLE
     * ES: FUNCIÓN PARA AÑADIR PERMISO A ROL
     *
     * @param RoleHasPermission $roleHasPermission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addPermission(RoleHasPermission $roleHasPermission): Role
    {
        if(!$this->permissions->contains($roleHasPermission)) {
            $roleHasPermission->setRole($this);
            $this->permissions->add($roleHasPermission);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE PERMISSION FROM ROLE
     * ES: FUNCIÓN PARA BORRAR PERMISO DE ROL
     *
     * @param RoleHasPermission $roleHasPermission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removePermission(RoleHasPermission $roleHasPermission): self
    {
        if($this->permissions->contains($roleHasPermission))
            $this->permissions->removeElement($roleHasPermission);
        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO SEE IF ROLE IS SUPERADMIN
     * ES: FUNCIÓN PARA VER SI EL ROL ES SUPERADMIN
     *
     * @return bool|null
     */
    // ----------------------------------------------------------------
    public function isSuperAdmin(): ?bool
    {
        if($this->getId() == self::ROLE_SUPERADMIN){
            return true;
        }
        return false;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO SEE IF ROLE IS TEACHER
     * ES: FUNCIÓN PARA VER SI EL ROL ES PROFESOR
     *
     * @return bool|null
     */
    // ----------------------------------------------------------------
    public function isTeacher(): ?bool
    {
        if($this->getId() == self::ROLE_TEACHER){
            return true;
        }
        return false;
    }
    // ----------------------------------------------------------------

}
