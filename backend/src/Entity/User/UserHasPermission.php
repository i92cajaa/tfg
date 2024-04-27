<?php


namespace App\Entity\User;


use App\Entity\Permission\Permission;
use App\Repository\UserHasPermissionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\UniqueConstraint(name:"permission_unique", columns: ["user_id", "permission_id"])]
#[ORM\Entity(repositoryClass: UserHasPermissionRepository::class)]
class UserHasPermission
{

    // ----------------------------------------------------------------
    // Primary Keys
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, cascade:["persist"], inversedBy: 'permissions')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Permission::class, cascade:["persist"], inversedBy: 'permissionUsers')]
    #[ORM\JoinColumn(name: "permission_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Permission $permission;

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Permission
     */
    public function getPermission(): Permission
    {
        return $this->permission;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param User $user
     * @return UserHasPermission
     */
    public function setUser(User $user): UserHasPermission
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param Permission $permission
     * @return UserHasPermission
     */
    public function setPermission(Permission $permission): UserHasPermission
    {
        $this->permission = $permission;
        return $this;
    }

}
