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
    const ROLE_SUPERADMIN = 1;

    const ROLE_JEFE_ESTUDIOS = 1;

    const ROLE_DIRECTOR = 2;

    const ROLE_MENTOR = 3;

    const ROLE_PROJECT = 4;

    const ROLE_SANDETEL = 5;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    // Campos

    #[ORM\Column(length: 180, nullable: false)]
    public string $name;

    #[ORM\Column(length: 180, nullable: false)]
    public string $color = '#000000';

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $admin;

    #[ORM\Column(length: 180, nullable: true)]
    public ?string $description;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"role", targetEntity: UserHasRole::class, cascade:["persist"])]
    private Collection $users;


    #[ORM\OneToMany(mappedBy:"role", targetEntity: RoleHasPermission::class, cascade:["persist"])]
    private Collection $permissions;

    public function __construct()
    {
        $this->users       = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return Role
     */
    public function setName(string $name): Role
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
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
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
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

    /**
     * @return ArrayCollection|Collection|UserHasRole|array
     */
    public function getUserHasRoles(): ArrayCollection|Collection|UserHasRole|array
    {
        return $this->users;
    }

    /**
     * @return UserHasRole|array|ArrayCollection
     */
    public function getUsers(): ArrayCollection|UserHasRole|array
    {
        $users = [];
        foreach ($this->getUserHasRoles() as $user) {
            $user    = $user->getUser();
            $users[] = $user;
        }

        return array_unique($users);;
    }

    /**
     * @param array|ArrayCollection|UserHasRole $users
     * @return Role
     */
    public function setUsers(ArrayCollection|UserHasRole|array $users): Role
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return ArrayCollection|Collection|array|RoleHasPermission
     */
    public function getPermissions(): ArrayCollection|Collection|array|RoleHasPermission
    {
        return $this->permissions;
    }

    /**
     * @param RoleHasPermission $roleHasPermission
     * @return Role
     */
    public function addPermission(RoleHasPermission $roleHasPermission): self
    {
        if(!$this->permissions->contains($roleHasPermission)) {
            $roleHasPermission->setRole($this);
            $this->permissions->add($roleHasPermission);
        }

        return $this;
    }


    public function removePermission(RoleHasPermission $roleHasPermission): self
    {
        if($this->permissions->contains($roleHasPermission))
            $this->permissions->removeElement($roleHasPermission);
        return $this;
    }

    public function getPermissionObjects():array
    {
        $permissions = [];
        foreach ($this->getPermissions() as $rolePermission) {
            $permission    = $rolePermission->getPermission();
            $permissions[] = $permission;
        }

        return $permissions;
    }

    /**
     * @return bool|null
     */
    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    /**
     * @param bool|null $admin
     * @return Role
     */
    public function setAdmin(?bool $admin): Role
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isProject(): ?bool
    {
        if($this->getId()==self::ROLE_PROJECT){
            return true;
        }
        return false;
    }


    /**
     * @return bool|null
     */
    public function isDirector(): ?bool
    {
        if($this->getId()==self::ROLE_DIRECTOR){
            return true;
        }
        return false;
    }

    /**
     * @return bool|null
     */
    public function isSandetel(): ?bool
    {
        if($this->getId()==self::ROLE_SANDETEL){
            return true;
        }
        return false;
    }

    /**
     * @return bool|null
     */
    public function isMentor(): ?bool
    {
        if($this->getId()==self::ROLE_MENTOR){
            return true;
        }
        return false;
    }




}
