<?php


namespace App\Repository;


use App\Entity\Permission\Permission;
use App\Entity\User\User;
use App\Entity\User\UserHasPermission;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserHasPermissionRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserHasPermission::class);
    }

    public function addUserPermission(User $user, Permission $permission): void
    {
        $newPermission = new UserHasPermission();
        $newPermission->setUser($user);
        $newPermission->setPermission($permission);
        $user->addPermission($newPermission);
        $this->save($this->_em, $user);
    }



    public function generateFromUserAndId(User $user, int $id): UserHasPermission
    {
        $newPermission = new UserHasPermission();
        $newPermission->setUser($user);
        $newPermission->setPermission($this->_em->getRepository(Permission::class)->find($id));
        return $newPermission;
    }
    public function generateFromUserAndPermission(User $user, Permission $permission): UserHasPermission
    {
        $newPermission = new UserHasPermission();
        $newPermission->setUser($user);
        $newPermission->setPermission($permission);
        return $newPermission;
    }
}