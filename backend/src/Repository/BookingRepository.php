<?php

namespace App\Repository;

use App\Entity\Client\Booking;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CREATE A NEW BOOKING
     * ES: FUNCIÓN PARA CREAR UNA RESERVA NUEVA
     *
     * @param Booking $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function save(Booking $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO DELETE A BOOKING
     * ES: FUNCIÓN PARA BORRAR UNA RESERVA
     *
     * @param Booking $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function remove(Booking $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A BOOKING'S DATA
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UNA RESERVA
     *
     * @param string $scheduleId
     * @param string $clientId
     * @param bool $array
     * @return array|Booking|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findByCompositeId(string $scheduleId, string $clientId, bool $array): array|Booking|null
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.client', 'client')
            ->leftJoin('b.schedule', 'schedule')
            ->leftJoin('schedule.lesson', 'lesson')
            ->addSelect('client')
            ->addSelect('schedule')
            ->addSelect('lesson')
            ->andWhere('client.id = :clientId')
            ->andWhere('schedule.id = :scheduleId')
            ->setParameter('clientId', $clientId)
            ->setParameter('scheduleId', $scheduleId)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO LIST ALL BOOKINGS
     * ES: FUNCIÓN PARA LISTAR TODAS LAS RESERVAS
     *
     * @param FilterService $filterService
     * @param bool $showAll
     * @param bool $array
     * @return array|null
     */
    // ----------------------------------------------------------------
    public function findBookings(FilterService $filterService, bool $showAll, bool $array = false): ?array
    {
        $query = $this->createQueryBuilder('b')
            ->leftJoin('b.client', 'client')
            ->leftJoin('b.schedule', 'schedule')
            ->leftJoin('schedule.lesson', 'lesson')
            ->addSelect('client')
            ->addSelect('schedule')
            ->addSelect('lesson')
        ;

        $this->setFilters($query, $filterService);
        $this->setOrders($query, $filterService);

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);

        if(!$showAll){
            $query->setMaxResults($filterService->limit);
        }

        // Pagination process
        $paginator = new Paginator($query);
        $paginator->getQuery()->setHydrationMode($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
        $totalRegisters = $paginator->count();

        $result = [];

        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);

        return [
            'totalRegisters'    => $totalRegisters,
            'bookings'          => $result,
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
            $client = $filterService->getFilterValue('client');
            if ($client !== null) {
                $query->andWhere('client.id = :client')
                    ->setParameter('client', $client);
            }

            $schedule = $filterService->getFilterValue('schedule');
            if ($schedule !== null) {
                $query->andWhere('schedule.id = :schedule')
                    ->setParameter('schedule', $schedule);
            }

            $lesson = $filterService->getFilterValue('lesson');
            if ($lesson !== null) {
                $query->andWhere('lesson.id = :lesson')
                    ->setParameter('lesson', $lesson);
            }

            $center = $filterService->getFilterValue('center');
            if ($center !== null) {
                $query->andWhere('lesson.center = :center')
                    ->setParameter('center', $center);
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
                    case "schedule-start":
                    case "schedule":
                        $query->orderBy('schedule.dateFrom', $order['order']);
                        break;
                    case "schedule-end":
                        $query->orderBy('schedule.dateTo', $order['order']);
                        break;
                    case "client":
                        $query->orderBy('CONCAT(client.name, client.surnames)', $order['order']);
                        break;
                    case "lesson":
                        $query->orderBy('lesson.name', $order['order']);
                        break;
                }
            }
        }
        else
        {
            $query->orderBy('schedule.dateFrom', 'DESC');
        }
    }
    // --------------------------------------------------------------
}