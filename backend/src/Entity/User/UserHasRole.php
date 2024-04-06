<?php


namespace App\Entity\User;


use App\Entity\Role\Role;
use App\Repository\UserHasRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\UniqueConstraint(name:"role_unique", columns: ["user_id", "role_id"])]
#[ORM\Entity(repositoryClass: UserHasRoleRepository::class)]
class UserHasRole
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, cascade:["persist"], inversedBy: 'roles')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Role::class, cascade:["persist"], inversedBy: 'users')]
    #[ORM\JoinColumn(name: "role_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Role $role;


    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserHasRole
     */
    public function setUser(User $user): UserHasRole
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Role
     */
    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * @param Role $role
     * @return UserHasRole
     */
    public function setRole(Role $role): UserHasRole
    {
        $this->role = $role;
        return $this;
    }

}
