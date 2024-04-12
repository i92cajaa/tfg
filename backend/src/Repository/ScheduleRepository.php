<?php

namespace App\Repository;

use App\Entity\Schedule\Schedule;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedule[]    findAll()
 * @method Schedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

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
    public function findSchedules(FilterService $filterService, bool $showAll): ?array
    {
        $query = $this->createQueryBuilder('sc')
            ->leftJoin('sc.lesson', 'lesson')
            ->leftJoin('sc.status', 'status')
            ->leftJoin('sc.room', 'room')
            ->leftJoin('sc.bookings', 'bookings')
            ->leftJoin('bookings.client', 'client')
            ->addSelect('lesson')
            ->addSelect('status')
            ->addSelect('room')
            ->addSelect('bookings')
            ->addSelect('client')
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
            'schedules'         => $result,
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
            $lesson = $filterService->getFilterValue('lesson');
            if ($lesson !== null) {
                $query->andWhere('sc.lesson = :lesson')
                    ->setParameter('lesson', $lesson);
            }

            $status = $filterService->getFilterValue('status');
            if ($status !== null) {
                $query->andWhere('sc.status = :status')
                    ->setParameter('status', $status);
            }

            $room = $filterService->getFilterValue('room');
            if ($room !== null) {
                $query->andWhere('sc.room = :room')
                    ->setParameter('room', $room);
            }

            $minDate = $filterService->getFilterValue('min_date');
            $maxDate = $filterService->getFilterValue('max_date');
            if ($minDate !== null && $maxDate !== null) {
                $dateFrom = \DateTime::createFromFormat('d-m-Y', $minDate)->setTime(0,0);
                $dateTo = \DateTime::createFromFormat('d-m-Y', $maxDate)->setTime(23, 59, 59);

                $query->andWhere('sc.dateFrom BETWEEN :dateFrom AND :dateTo OR sc.dateTo BETWEEN :dateFrom AND :dateTo')
                    ->setParameter('dateFrom', $dateFrom)
                    ->setParameter('dateTo', $dateTo);
            } elseif ($minDate !== null) {
                $dateFrom = \DateTime::createFromFormat('d-m-Y', $minDate)->setTime(0,0);

                $query->andWhere('sc.dateFrom >= :dateFrom')
                    ->setParameter('dateFrom', $dateFrom);
            } elseif ($maxDate !== null) {
                $dateTo = \DateTime::createFromFormat('d-m-Y', $maxDate)->setTime(23, 59, 59);

                $query->andWhere('sc.dateTo <= :dateTo')
                    ->setParameter('dateTo', $dateTo);
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
                        $query->orderBy('sc.id', $order['order']);
                        break;
                    case "lesson":
                        $query->orderBy('sc.lesson', $order['order']);
                        break;
                    case "status":
                        $query->orderBy('sc.status', $order['order']);
                        break;
                    case "room":
                        $query->orderBy('room.number', $order['order']);
                        break;
                    case "bookings":
                        $query->orderBy('COUNT(sc.bookings)', $order['order']);
                        break;
                    case "dateFrom":
                        $query->orderBy('sc.dateFrom', $order['order']);
                        break;
                    case "dateTo":
                        $query->orderBy('sc.dateTo', $order['order']);
                }
            }
        }
        else
        {
            $query->orderBy('sc.dateFrom', 'DESC');
        }
    }
    // --------------------------------------------------------------
}