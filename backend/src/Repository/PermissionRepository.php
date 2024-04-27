<?php


namespace App\Repository;


use App\Entity\Permission\Permission;
use App\Entity\Permission\PermissionGroup;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use App\Service\FilterService;
use Doctrine\Persistence\ManagerRegistry;

class PermissionRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permission::class);
    }

    public function getPermissionByTaskAndGroupName(string $action, string $groupName)
    {
        try {
            return $this->createQueryBuilder('p')
                ->select('p')
                ->leftJoin('p.group', 'permissionGroup')
                ->addSelect('permissionGroup')
                ->andWhere('permissionGroup.name LIKE :groupName')
                ->andWhere('p.action LIKE :action')
                ->setParameter('action', $action)
                ->setParameter('groupName', $groupName)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function createPermission(PermissionGroup $group, string $action, string $label, ?string $description = null): Permission
    {
        $permission = new Permission();
        $permission->setGroup($group);
        $permission->setAction($action);
        $permission->setLabel($label);
        $permission->setDescription($description);
        $this->save($this->_em, $permission);
        return $permission;
    }

    public function updatePermission(Permission $permission, string $action, string $label, ?string $description = null, ?PermissionGroup $group = null): Permission
    {
        $permission->setAction($action)
            ->setLabel($label)
            ->setDescription($description ?: $permission->getDescription())
            ->setGroup($group ?: $permission->getGroup());
        $this->save($this->_em, $permission);
        return $permission;
    }

    public function deletePermission(Permission $permission): void
    {
        $this->delete($this->_em, $permission);
    }

    public function addFilters(QueryBuilder &$query, FilterService $filterService): void
    {
        if ($filterService->getFilters()) {
            if ($search = $filterService->getFilterValue('search')) {
                $query->andWhere('role.name LIKE :search OR role.description LIKE :search');
                $query->setParameter('search', "%$search%");
            }
        }
    }

    public function addOrders(QueryBuilder &$query, FilterService $filterService): void
    {
        if ($orders = $filterService->getOrders()) {
            $field          = null;
            $orderDirection = null;
            foreach ($orders as $order) {
                $orderDirection = $order['order'];
                switch ($order['field']) {
                    case "id":
                        $field = "role.id";
                        break;
                    case "name":
                        $field = "role.name";
                        break;
                    case "description":
                        $field = "role.description";
                        break;
                }
            }
            $query->addOrderBy($field, $orderDirection);
        }
    }
}