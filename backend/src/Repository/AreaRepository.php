<?php

namespace App\Repository;


use App\Entity\Area\Area;
use App\Service\FilterService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Area>
 *
 * @method Area|null find($id, $lockMode = null, $lockVersion = null)
 * @method Area|null findOneBy(array $criteria, array $orderBy = null)
 * @method Area[]    findAll()
 * @method Area[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AreaRepository  extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Area::class);
    }

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CREATE A NEW AREA
     * ES: FUNCIÓN PARA CREAR UN ÁREA NUEVA
     *
     * @param Area $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function save(Area $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO DELETE AN AREA
     * ES: FUNCIÓN PARA BORRAR UN ÁREA
     *
     * @param Area $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function remove(Area $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET AN AREA'S DATA
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UN ÁREA
     *
     * @param string $id
     * @param bool $array
     * @return array|Area|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findById(string $id, bool $array): array|Area|null
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.centers', 'centers')
            ->addSelect('centers')
            ->andWhere('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET AN AREA'S DATA (SIMPLE METHOD)
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UN ÁREA (MÉTODO SIMPLE)
     *
     * @param string $id
     * @param bool $array
     * @return array|Area
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findByIdSimple(string $id, bool $array): array|Area
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO LIST ALL AREAS
     * ES: FUNCIÓN PARA LISTAR TODAS LAS ÁREAS
     *
     * @param FilterService $filterService
     * @param bool $showAll
     * @return array|null
     */
    // ----------------------------------------------------------------
    public function findAreas(FilterService $filterService, bool $showAll): ?array
    {
        $query = $this->createQueryBuilder('a')
            ->leftJoin('a.centers', 'centers')
            ->addSelect('centers')
        ;

        $this->setFilters($query, $filterService);
        $this->setOrders($query, $filterService);

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);

        if(!$showAll){
            $query->setMaxResults($filterService->limit);
        }

        // Pagination process
        $paginator = new Paginator($query);
        $paginator->getQuery()->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
        $totalRegisters = $paginator->count();

        $result = [];

        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);

        return [
            'totalRegisters'    => $totalRegisters,
            'areas'             => $result,
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
            $search_array = $filterService->getFilterValue('search_array');
            if ($search_array != null)
            {
                $array_values = explode(' ', $search_array);

                $conditions = [];
                $parameters = [];

                foreach ($array_values as $index => $value)
                {
                    $param = 'search' . $index;
                    $conditions[] = 'a.name LIKE :' . $param . 'OR a.city LIKE :' . $param;
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
                        $query->orderBy('a.id', $order['order']);
                        break;
                    case "name":
                        $query->orderBy('a.name', $order['order']);
                        break;
                    case "city":
                        $query->orderBy('a.city', $order['order']);
                        break;
                }
            }
        }
        else
        {
            $query->orderBy('a.name', 'DESC');
        }
    }
    // --------------------------------------------------------------
}