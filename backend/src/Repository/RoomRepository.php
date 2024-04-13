<?php

namespace App\Repository;

use App\Entity\Room\Room;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Room|null find($id, $lockMode = null, $lockVersion = null)
 * @method Room|null findOneBy(array $criteria, array $orderBy = null)
 * @method Room[]    findAll()
 * @method Room[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CREATE A NEW ROOM
     * ES: FUNCIÓN PARA CREAR UNA HABITACIÓN NUEVA
     *
     * @param Room $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function save(Room $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO DELETE A ROOM
     * ES: FUNCIÓN PARA BORRAR UNA HABITACIÓN
     *
     * @param Room $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function remove(Room $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A ROOM'S DATA
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UNA HABITACIÓN
     *
     * @param string $id
     * @param bool $array
     * @return array|Room|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findById(string $id, bool $array): array|Room|null
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.center', 'center')
            ->leftJoin('r.schedules', 'schedules')
            ->addSelect('center')
            ->addSelect('schedules')
            ->andWhere('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A ROOM'S DATA (SIMPLE METHOD)
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UNA HABITACIÓN (MÉTODO SIMPLE)
     *
     * @param string $id
     * @param bool $array
     * @return array|Room|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findByIdSimple(string $id, bool $array): array|Room|null
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.id = :id')
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
    public function findRooms(FilterService $filterService, bool $showAll): ?array
    {
        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.center', 'center')
            ->leftJoin('r.schedules', 'schedules')
            ->addSelect('center')
            ->addSelect('schedules')
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
            'rooms'             => $result,
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
            $center = $filterService->getFilterValue('center');
            if ($center !== null) {
                $query->andWhere('l.center = :center')
                    ->setParameter('center', $center);
            }

            $floor = $filterService->getFilterValue('floor');
            if ($floor !== null) {
                $query->andWhere('r.floor = :floor')
                    ->setParameter('floor', $floor);
            }

            $room = $filterService->getFilterValue('room');
            if ($room !== null) {
                $query->andWhere('r.room = :room')
                    ->setParameter('room', $room);
            }

            $minCapacity = $filterService->getFilterValue('min_capacity');
            $maxCapacity = $filterService->getFilterValue('max_capacity');
            if ($minCapacity !== null && $maxCapacity !== null) {
                $query->andWhere('r.capacity BETWEEN :minCapacity AND :maxCapacity')
                    ->setParameter('minCapacity', $minCapacity)
                    ->setParameter('maxCapacity', $maxCapacity);
            } elseif ($minCapacity !== null) {
                $query->andWhere('r.capacity >= :minCapacity')
                    ->setParameter('minCapacity', $minCapacity);
            } elseif ($maxCapacity !== null) {
                $query->andWhere('r.capacity <= :maxCapacity')
                    ->setParameter('maxCapacity', $maxCapacity);
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
                        $query->orderBy('r.id', $order['order']);
                        break;
                    case "center":
                        $query->orderBy('center.name', $order['order']);
                        break;
                    case "floor":
                        $query->orderBy('r.floor', $order['order']);
                        break;
                    case "room":
                        $query->orderBy('r.room', $order['order']);
                        break;
                    case "capacity":
                        $query->orderBy('r.capacity', $order['order']);
                        break;
                }
            }
        }
        else
        {
            $query->orderBy('center.name', 'DESC');
        }
    }
    // --------------------------------------------------------------
}