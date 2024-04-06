<?php


namespace App\Entity\Role;

use App\Entity\Permission\Permission;
use App\Repository\RoleHasPermissionRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;


#[ORM\UniqueConstraint(name:"role_permission_unique", columns: ["role_id", "permission_id"])]
#[ORM\Entity(repositoryClass: RoleHasPermissionRepository::class)]
class RoleHasPermission
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Role::class, cascade:["persist"], inversedBy: 'users')]
    #[ORM\JoinColumn(name: "role_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Role $role;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Permission::class, cascade:["persist"], inversedBy: 'permissionRoles')]
    #[ORM\JoinColumn(name: "permission_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Permission $permission;

    /**
     * @return Role
     */
    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * @param Role $role
     * @return RoleHasPermission
     */
    public function setRole(Role $role): RoleHasPermission
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return Permission
     */
    public function getPermission(): Permission
    {
        return $this->permission;
    }

    /**
     * @param Permission $permission
     * @return RoleHasPermission
     */
    public function setPermission(Permission $permission): RoleHasPermission
    {
        $this->permission = $permission;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getId().'';
    }

}
