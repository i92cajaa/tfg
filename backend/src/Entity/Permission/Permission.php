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

    #[ORM\ManyToOne(targetEntity: PermissionGroup::class, inversedBy: 'permissions')]
    #[ORM\JoinColumn(name: "group_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private PermissionGroup $group;

    #[ORM\OneToMany(mappedBy:"permission", targetEntity: UserHasPermission::class)]
    private array|Collection $permissionUsers;

    #[ORM\OneToMany(mappedBy:"permission", targetEntity: RoleHasPermission::class)]
    private array|Collection $permissionRoles;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(type:'string' ,length: 180, nullable: false)]
    private string $label;

    #[ORM\Column(type:'string', length: 180, nullable: false)]
    private string $action;

    #[ORM\Column(type:'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'boolean', length: 180, nullable: false, options:['default' => false])]
    private bool $adminManaged = false;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->permissionUsers = new ArrayCollection();
        $this->permissionRoles = new ArrayCollection();
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
     * @return PermissionGroup
     */
    public function getGroup(): PermissionGroup
    {
        return $this->group;
    }

    /**
     * @return array|Collection
     */
    public function getPermissionUsers(): array|Collection
    {
        return $this->permissionUsers;
    }

    /**
     * @return array|Collection
     */
    public function getPermissionRoles(): array|Collection
    {
        return $this->permissionRoles;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isAdminManaged(): bool
    {
        return $this->adminManaged;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

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
     * @param array|Collection $permissionUsers
     * @return Permission
     */
    public function setPermissionUsers(array|Collection $permissionUsers): Permission
    {
        $this->permissionUsers = $permissionUsers;
        return $this;
    }

    /**
     * @param array|Collection $permissionRoles
     * @return Permission
     */
    public function setPermissionRoles(array|Collection $permissionRoles): Permission
    {
        $this->permissionRoles = $permissionRoles;
        return $this;
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
     * @param string $action
     * @return Permission
     */
    public function setAction(string $action): Permission
    {
        $this->action = $action;
        return $this;
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
     * @param bool $adminManaged
     * @return Permission
     */
    public function setAdminManaged(bool $adminManaged): Permission
    {
        $this->adminManaged = $adminManaged;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD USER TO PERMISSION
     * ES: FUNCIÓN PARA AÑADIR USUARIO A PERMISO
     *
     * @param UserHasPermission $userHasPermission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addUserPermission(UserHasPermission $userHasPermission): Permission
    {
        if (!$this->permissionUsers->contains($userHasPermission)) {
            $this->permissionUsers->add($userHasPermission);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE USER FROM PERMISSION
     * ES: FUNCIÓN PARA BORRAR USUARIO DE PERMISO
     *
     * @param UserHasPermission $userHasPermission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeUserPermission(UserHasPermission $userHasPermission): Permission
    {
        if ($this->permissionUsers->contains($userHasPermission)) {
            $this->permissionUsers->removeElement($userHasPermission);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD ROLE TO PERMISSION
     * ES: FUNCIÓN PARA AÑADIR ROL A PERMISO
     *
     * @param RoleHasPermission $roleHasPermission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addRolePermission(RoleHasPermission $roleHasPermission): Permission
    {
        if (!$this->permissionRoles->contains($roleHasPermission)) {
            $this->permissionRoles->add($roleHasPermission);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE ROLE FROM PERMISSION
     * ES: FUNCIÓN PARA BORRAR ROL DE PERMISO
     *
     * @param RoleHasPermission $roleHasPermission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeRolePermission(RoleHasPermission $roleHasPermission): Permission
    {
        if ($this->permissionRoles->contains($roleHasPermission)) {
            $this->permissionRoles->removeElement($roleHasPermission);
        }

        return $this;
    }
    // ----------------------------------------------------------------

}
