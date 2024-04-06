<?php

namespace App\Entity\Permission;

use App\Entity\Role\RoleHasPermission;
use App\Entity\User\UserHasPermission;
use App\Repository\PermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: PermissionRepository::class)]
class Permission
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    // Relaciones

    #[ORM\ManyToOne(targetEntity: PermissionGroup::class, inversedBy: 'permissions')]
    #[ORM\JoinColumn(name: "group_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private PermissionGroup $group;

    // Campos

    #[ORM\Column(type:'string' ,length: 180, nullable: false)]
    private string $label;

    #[ORM\Column(type:'string', length: 180, nullable: false)]
    private string $action;

    #[ORM\Column(type:'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'boolean', length: 180, nullable: false, options:['default' => false])]
    private bool $adminManaged = false;

    #[ORM\Column(name:"module_dependant", type: 'string', length: 200, nullable: true)]
    private ?string $moduleDependant = null;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"permission", targetEntity: UserHasPermission::class)]
    private Collection $permissionUsers;

    #[ORM\OneToMany(mappedBy:"permission", targetEntity: RoleHasPermission::class)]
    private Collection $permissionRoles;

    public function __construct()
    {
        $this->permissionUsers = new ArrayCollection();
        $this->permissionRoles = new ArrayCollection();
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
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return Permission
     */
    public function setAction(string $action): Permission
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return Permission
     */
    public function setLabel(string $label): Permission
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAdminManaged(): bool
    {
        return $this->adminManaged;
    }

    /**
     * @param bool $adminManaged
     * @return Permission
     */
    public function setAdminManaged(bool $adminManaged): Permission
    {
        $this->adminManaged = $adminManaged;
        return $this;
    }

    /**
     * @return PermissionGroup
     */
    public function getGroup(): PermissionGroup
    {
        return $this->group;
    }

    /**
     * @param PermissionGroup $group
     * @return Permission
     */
    public function setGroup(PermissionGroup $group): Permission
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPermissionUsers(): ArrayCollection
    {
        return $this->permissionUsers;
    }

    /**
     * @param ArrayCollection|Collection $permissionUsers
     * @return Permission
     */
    public function setPermissionUsers(ArrayCollection|Collection $permissionUsers): self
    {
        $this->permissionUsers = $permissionUsers;
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
     * @return Permission
     */
    public function setDescription(?string $description): Permission
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return ArrayCollection|Collection
     */
    public function getPermissionRoles(): ArrayCollection|Collection
    {
        return $this->permissionRoles;
    }

    /**
     * @param ArrayCollection|Collection $permissionRoles
     * @return Permission
     */
    public function setPermissionRoles($permissionRoles): self
    {
        $this->permissionRoles = $permissionRoles;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getModuleDependant(): ?string
    {
        return $this->moduleDependant;
    }

    /**
     * @param string|null $moduleDependant
     * @return Permission
     */
    public function setModuleDependant(?string $moduleDependant): Permission
    {
        $this->moduleDependant = $moduleDependant;
        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

}
