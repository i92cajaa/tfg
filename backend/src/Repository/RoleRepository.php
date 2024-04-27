<?php


namespace App\Repository;



use App\Entity\Role\Role;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


class RoleRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    public function findRoles(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('role')
            ->select('role')
            ->leftJoin('role.users', 'role_has_user')
            ->addSelect('role_has_user')
            ->leftJoin('role_has_user.user', 'user')
            ->addSelect('user');
        ;

        $this->addFilters($query, $filterService);
        $this->addOrders($query, $filterService);

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1) * $filterService->limit) : $filterService->page - 1);

        return $this->paginateQueryBuilderResults($query, $filterService, true);
    }

    public function findRoleById($roleId): ?Role
    {
        return $this->createQueryBuilder('role')
                    ->select('role')
                    ->leftJoin('role.permissions', 'role_permissions')
                    ->addSelect('role_permissions')
                    ->leftJoin('role_permissions.permission', 'permission')
                    ->addSelect('permission')
                    ->leftJoin('role.users', 'role_users')
                    ->addSelect('role_users')
                    ->leftJoin('role_users.user', 'user')
                    ->addSelect('user')
                    ->where('role.id = :id')
                    ->setParameter('id', $roleId)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function search(FilterService $filterService)
    {
        $query = $this->_em->getRepository(Role::class)
            ->createQueryBuilder('role')
            ->select('role')
            ->leftJoin('role.users', 'role_has_user')
            ->addSelect('role_has_user')
            ->leftJoin('role_has_user.user', 'user')
            ->addSelect('user');

        $this->addFilters($query, $filterService);
        $this->addOrders($query, $filterService);

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1) * $filterService->limit) : $filterService->page - 1);

        return $this->paginateQueryBuilderResults($query, $filterService, true);
    }

    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return $this->findRoleById($id);
    }

    public function getRolesByIds(?array $roleIds): ?array
    {
        return $this->createQueryBuilder('role')
                    ->select('role')
                    ->where('role.id IN(:ids)')
                    ->setParameter('ids', $roleIds)
                    ->getQuery()
                    ->getResult();
    }

    public function getRolesExceptSuperAdmin(): ?array
    {
        return $this->createQueryBuilder('role')
            ->select('role')
            ->where('role.id != 1')
            ->getQuery()
            ->getResult();
    }


    public function createRole(string $name, string $color, ?string $description = null): Role
    {
        $role = new Role();
        $role->setName($name)
             ->setColor($color)
             ->setDescription($description);
        $this->save($this->_em, $role);
        return $role;
    }

    public function updateRole(Role $role, string $name, string $color, ?string $description = null): Role
    {
        $role->setName($name);
        $role->setColor($color);
        $role->setDescription($description ?: $role->getDescription());
        $this->save($this->_em, $role);
        return $role;
    }

    public function deleteRole(Role $role)
    {
        $this->delete($this->_em, $role);
    }

    public function addFilters(QueryBuilder &$query, FilterService $filterService): void
    {
        if ($filterService->getFilters()) {
            if ($search = $filterService->getFilterValue('search')) {
                $query->andWhere('
                    role.name LIKE :search
                    OR role.description LIKE :search
                ');
                $query->setParameter('search', "%$search%");
            }
            if($filterService->getFilterValue('users') != null){
                $where = 'user.id LIKE ';
                $users = [];
                foreach ($filterService->getFilterValue('users') as $index=>$user){
                    if($index == 0){
                        $where = $where.':user'.$index;

                    }else{
                        $where = $where.' OR user.id LIKE :user'.$index;
                    }
                    array_push($users, $user);
                }
                $query->orWhere($where);
                foreach ($users as $index=>$user){
                    $query->setParameter('user'.$index, "%".$user."%");
                }

            }

            if($filterService->getFilterValue('name') != null){
                $query->andWhere('role.name LIKE :name')
                    ->setParameter('name', "%".$filterService->getFilterValue('name')."%");
            }

            if($filterService->getFilterValue('color') != null){
                $query->andWhere('role.color LIKE :color')
                    ->setParameter('color', "%".$filterService->getFilterValue('color')."%");
            }
        }
    }

    public function addOrders(QueryBuilder &$query, FilterService $filterService): void
    {
        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {

                switch ($order['field']) {
                    case "name":
                        $query->orderBy('role.name', $order['order']);
                        break;
                    case "description":
                        $query->orderBy('role.description', $order['order']);
                        break;
                    case "id":
                        $query->orderBy('role.id', $order['order']);
                        break;
                    case "color":
                        $query->orderBy('role.color', $order['order']);
                        break;
                    case "users":
                        $query->orderBy('user.name', $order['order']);
                        break;
                }
            }
        }
    }
}
