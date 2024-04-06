<?php


namespace App\Repository;


use App\Entity\Permission\Permission;
use App\Entity\Role\Role;
use App\Entity\Role\RoleHasPermission;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class RoleHasPermissionRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoleHasPermission::class);
    }


    public function createRolePermission(Role $role, Permission $permission)
    {
        $rolePermission = new RoleHasPermission();
        $rolePermission->setRole($role);
        $rolePermission->setPermission($permission);
        $this->save($this->_em, $rolePermission);
    }

    public function deleteRolePermission(RoleHasPermission $rolePermission) {
        $this->delete($this->_em, $rolePermission);
    }

    public function addFilters(QueryBuilder &$query, FilterService $filterService): void
    {
        // TODO: Implement addFilters() method.
    }

    public function addOrders(QueryBuilder &$query, FilterService $filterService): void
    {
        // TODO: Implement addOrders() method.
    }
}