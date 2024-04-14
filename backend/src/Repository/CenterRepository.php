<?php

namespace App\Repository;

use App\Entity\Center\Center;
use App\Service\FilterService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Center>
 *
 * @method Center|null find($id, $lockMode = null, $lockVersion = null)
 * @method Center|null findOneBy(array $criteria, array $orderBy = null)
 * @method Center[]    findAll()
 * @method Center[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CenterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Center::class);
    }

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CREATE A NEW CENTER
     * ES: FUNCIÓN PARA CREAR UN CENTRO NUEVO
     *
     * @param Center $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function save(Center $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO DELETE A CENTER
     * ES: FUNCIÓN PARA BORRAR UN CENTRO
     *
     * @param Center $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function remove(Center $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A CENTER'S DATA
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UN CENTRO
     *
     * @param string $id
     * @param bool $array
     * @return array|Center|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findById(string $id, bool $array): array|Center|null
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.logo', 'logo')
            ->leftJoin('c.users', 'users')
            ->leftJoin('c.area', 'area')
            ->leftJoin('c.lessons', 'lessons')
            ->leftJoin('c.rooms', 'rooms')
            ->addSelect('logo')
            ->addSelect('users')
            ->addSelect('area')
            ->addSelect('lessons')
            ->addSelect('rooms')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO LIST ALL ROOMS
     * ES: FUNCIÓN PARA LISTAR TODAS LAS HABITACIONES
     *
     * @param FilterService $filterService
     * @param bool $showAll
     * @return array|null
     */
    // ----------------------------------------------------------------
    public function findCenters(FilterService $filterService, bool $showAll): ?array
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.logo', 'logo')
            ->leftJoin('c.users', 'users')
            ->leftJoin('c.area', 'area')
            ->leftJoin('c.lessons', 'lessons')
            ->leftJoin('c.rooms', 'rooms')
            ->addSelect('logo')
            ->addSelect('users')
            ->addSelect('area')
            ->addSelect('lessons')
            ->addSelect('rooms')
        ;

        $this->setFilters($query, $filterService);
        $this->setOrders($query, $filterService);

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);

        if(!$showAll){
            $query->setMaxResults($filterService->limit);
        }

        // Pagination process
        $paginator = new Paginator($query);
        $paginator->getQuery()->setHydrationMode(AbstractQuery::HYDRATE_OBJECT);
        $totalRegisters = $paginator->count();

        $result = [];

        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);

        return [
            'totalRegisters'    => $totalRegisters,
            'centers'           => $result,
            'lastPage'          => $lastPage,
            'filters'           => $filterService->getAll()
        ];
    }
    // ----------------------------------------------------------------

    // --------------------------------------------------------------
    /**
     * EN: FUNCTION TO SET FILTERS
     * ES: FUNCIÓN PARA ESTABLECER FILTROS
     *
     * @param QueryBuilder $query
     * @param FilterService $filterService
     * @return void
     */
    // --------------------------------------------------------------
    public function setFilters(QueryBuilder $query, FilterService $filterService): void
    {
        if (count($filterService->getFilters()) > 0)
        {
            $area = $filterService->getFilterValue('area');
            if ($area !== null) {
                $query->andWhere('c.area = :area')
                    ->setParameter('area', $area);
            }

            $search_array = $filterService->getFilterValue('search_array');
            if ($search_array != null)
            {
                $array_values = explode(' ', $search_array);

                $conditions = [];
                $parameters = [];

                foreach ($array_values as $index => $value)
                {
                    $param = 'search' . $index;
                    $conditions[] = 'c.name LIKE :' . $param . 'OR c.address LIKE :' . $param . 'OR c.phone LIKE :' . $param;
                    $parameters[$param] = '%' . $value . '%';
                }

                if (!empty($conditions))
                {
                    $query->andWhere(implode(' AND ', $conditions));

                    foreach($parameters as $key => $value)
                    {
                        $query->setParameter($key, $value);
                    }
                }
            }
        }
    }
    // --------------------------------------------------------------

    // --------------------------------------------------------------
    /**
     * EN: FUNCTION TO SET ORDER
     * ES: FUNCIÓN PARA ESTABLECER ORDEN
     *
     * @param QueryBuilder $query
     * @param FilterService $filterService
     * @return void
     */
    // --------------------------------------------------------------
    public function setOrders(QueryBuilder $query, FilterService $filterService): void
    {
        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order)
            {
                switch ($order['field'])
                {
                    case "id":
                        $query->orderBy('c.id', $order['order']);
                        break;
                    case "name":
                        $query->orderBy('c.name', $order['order']);
                        break;
                    case "address":
                        $query->orderBy('c.address', $order['order']);
                        break;
                    case "phone":
                        $query->orderBy('c.phone', $order['order']);
                        break;
                    case "area":
                        $query->orderBy('area.name', $order['order']);
                        break;
                }
            }
        }
        else
        {
            $query->orderBy('c.name', 'DESC');
        }
    }
    // --------------------------------------------------------------
}
