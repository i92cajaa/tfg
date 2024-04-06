<?php


namespace App\Repository;


use App\Entity\Permission\PermissionGroup;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Service\FilterService;
use Doctrine\Persistence\ManagerRegistry;

class PermissionGroupRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermissionGroup::class);
    }

    public function search(FilterService $filterService)
    {
        $query = $this->_em->getRepository(PermissionGroup::class)
            ->createQueryBuilder('gp')
            ->select('gp')
            ->leftJoin('gp.permissions', 'permissions')
            ->addSelect('permissions');

        $this->addFilters($query, $filterService);
        $this->addOrders($query, $filterService);

        return $this->paginateQueryBuilderResults($query, $filterService, true);
    }

    public function getAvailablePermission()
    {
        return $this->_em->getRepository(PermissionGroup::class)
            ->createQueryBuilder('gp')
            ->select('gp')
            ->leftJoin('gp.permissions', 'permissions')
            ->addSelect('permissions')
            ->getQuery()
            ->getResult();
    }

    public function getAvailablePermissionForNonSuperAdmin(array $modules)
    {
        return $this->_em->getRepository(PermissionGroup::class)
            ->createQueryBuilder('gp')
            ->select('gp')
            ->join('gp.permissions', 'permissions', 'WITH', 'permissions.adminManaged = 0 AND (permissions.moduleDependant IN(:modules) OR permissions.moduleDependant IS NULL)')
            ->addSelect('permissions')
            ->setParameter('modules', $modules)
            ->getQuery()
            ->getResult();
    }

    /**
     * Función para añadir los filtros a la consulta principal. del search.
     *
     * @param QueryBuilder $query
     * @param FilterService $filterService
     */
    public function addFilters(QueryBuilder &$query, FilterService $filterService): void
    {
        if ($filterService->getFilters()) {
            if ($search = $filterService->getFilterValue('search')) {
                $query->andWhere('gp.name LIKE :search OR gp.label LIKE :search');
                $query->setParameter('search', "%$search%");
            }
        }
    }


    /**
     * Función para añadir el orden a los resultados devueltos por la query.
     *
     * @param QueryBuilder $query
     * @param FilterService $filterService
     */
    public function addOrders(QueryBuilder &$query, FilterService $filterService): void
    {
        if ($orders = $filterService->getOrders()) {
            $field          = null;
            $orderDirection = null;
            foreach ($orders as $order) {
                $orderDirection = $order['order'];
                switch ($order['field']) {
                    case "id":
                        $field = "gp.id";
                        break;
                    case "name":
                        $field = "gp.name";
                        break;
                    case "label":
                        $field = "gp.label";
                        break;
                }
            }
            $query->addOrderBy($field, $orderDirection);
        }
    }


    public function createGroup(string $groupName, string $groupLabel)
    {
        $group = new PermissionGroup();
        $group->setName($groupName);
        $group->setLabel($groupLabel);
        $this->save($this->_em, $group);
        return $group;
    }

    /**
     * @param PermissionGroup|object $group
     */
    public function deleteGroup(PermissionGroup $group)
    {
        $this->delete($this->_em, $group);
    }


    public function updateGroup(PermissionGroup $group, string $name, string $label): PermissionGroup
    {
        $group->setName($name);
        $group->setLabel($label);
        $this->save($this->_em, $group);

        return $group;
    }


    public function persist(PermissionGroup $group, ?string $name=null, ?string $label=null): PermissionGroup
    {
        if ($name != null)$group->setName($name);
        if ($label != null)$group->setLabel($label);
        $this->save($this->_em, $group);

        return $group;
    }
}