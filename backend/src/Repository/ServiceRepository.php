<?php

namespace App\Repository;

use Doctrine\ORM\Query\Expr\Join;
use App\Entity\Service\Service;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use DateTime;
use Doctrine\ORM\Query\Expr;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function getServicesByScheduleDates(Datetime $datetime, string $clientId)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.professionals', 'userHasService')
            ->addSelect('userHasService')
            ->leftJoin('userHasService.user', 'user')
            ->addSelect('user')
            ->leftJoin('user.clients', 'userHasClient')
            ->addSelect('userHasClient')
            ->leftJoin('userHasClient.client', 'client')
            ->addSelect('client')
            ->leftJoin('user.schedules', 'schedules')
            ->addSelect('schedules')
            ->where('schedules.weekDay = :weekDay')
            ->andWhere('client.id LIKE :client')
            ->setParameter('weekDay', $datetime->format('w'))
            ->setParameter('client', $clientId)
            ->getQuery()->getArrayResult();
    }

    public function findServicesByIds($servicesIds)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.professionals', 'user')
            ->leftJoin('s.division', 'division')
            ->where('s.id IN(:ids)')
            ->setParameter('ids', $servicesIds)
            ->getQuery()->getResult();
    }

    public function getServicesByClient(string $clientId): array|float|int|string
    {
        return $this->createQueryBuilder('s')
            ->join('s.professionals', 'userHasService')
            ->join('userHasService.user', 'user')
            ->join('user.clients', 'userHasClient')
            ->join('userHasClient.client', 'client', 'WITH', 'client.id LIKE :client')
            ->leftJoin('s.division', 'division')
            ->setParameter('client', $clientId)
            ->getQuery()->getArrayResult();
    }

    public function getServiceAppointmentsCount(?DateTime $startDate = null, ?DateTime $endDate = null)
    {

        $query = $this->createQueryBuilder('s')
            ->select('s.name AS service_name', 's.color AS service_color', 'COUNT(ahs.appointment) AS appointment_count')
            ->leftJoin('s.appointments', 'ahs')
            ->leftJoin('ahs.appointment', 'a');

        if ($startDate !== null && $endDate !== null) {
            $query
                ->andWhere('a.timeFrom BETWEEN :timeFrom AND :timeTo')
                ->setParameter('timeFrom', $startDate)
                ->setParameter('timeTo', $endDate);
        }

        $query->groupBy('s.id');

        return $query->getQuery()->getResult();
    }


    public function getMentorshipCountByMonth($customStartDate = null)
    {
       
        if ($customStartDate) {
            $endDate = $customStartDate;
            $startDate = clone $endDate;
            $startDate->modify('-11 months'); 
            $startDate = $startDate->format('Y-m-d');
        } else {
           
            $endDate = new \DateTime();
            $startDate = clone $endDate;
            $startDate->modify('-11 months'); 
            $startDate = $startDate->format('Y-m-d');
        }
    
        $sql = "SELECT
                    CONCAT(YEAR(a.time_from), '-', LPAD(MONTH(a.time_from), 2, '0')) AS month_year,
                    COUNT(ahs.appointment_id) AS appointment_count
                FROM
                    service s
                    LEFT JOIN appointment_has_service ahs ON s.id = ahs.service_id
                    LEFT JOIN appointment a ON ahs.appointment_id = a.id
                WHERE
                    a.time_from BETWEEN :startDate AND :endDate
                GROUP BY
                    month_year
                ORDER BY
                    month_year ASC";
    
        $entityManager = $this->getEntityManager();
    
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('month_year', 'month_year');
        $rsm->addScalarResult('appointment_count', 'appointment_count');
    
        $query = $entityManager->createNativeQuery($sql, $rsm);
    
        $query->setParameter('startDate', $startDate);
        $query->setParameter('endDate', $endDate->format('Y-m-d'));
    
        return $query->getResult();
    }
    



    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return Service[] Returns an array of Festive objects
     */
    public function findServices(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('s')->leftJoin('s.professionals', 'userHasService')
            ->addSelect('userHasService')
            ->leftJoin('userHasService.user', 'user')
            ->addSelect('user')
            ->leftJoin('s.division', 'division');

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1) * $filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {

            if ($filterService->getFilterValue('info') != null) {
                $query->andWhere('CONCAT(s.name, s.price, s.color) LIKE :info')
                    ->setParameter('info', "%" . $filterService->getFilterValue('info') . "%");
            }
        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('s.name', $order['order']);
                        break;
                    case "user":
                        $query->orderBy('user.name', $order['order']);
                        break;
                    case "type":
                        $query->orderBy('s.type', $order['order']);
                        break;
                    case "division":
                        $query->orderBy('division.name', $order['order']);
                        break;
                    case "color":
                        $query->orderBy('s.color', $order['order']);
                        break;
                    case "iva":
                        $query->orderBy('s.iva', $order['order']);
                        break;
                    case "price":
                        $query->orderBy('s.price', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('s.id', 'DESC');
        }

        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        $lastPage = (int)ceil($totalRegisters / $filterService->limit);
        //$users = $query->getQuery()->getResult();

        return [
            'totalRegisters' => $totalRegisters,
            'data'           => $result,
            'lastPage'       => $lastPage
        ];
    }

    public function persist(Service $service)
    {
        $this->save($this->_em, $service);
    }

    public function remove(Service $service)
    {
        $this->delete($this->_em, $service);
    }

    public function setActive(Service $service, bool $status)
    {
        $service->setActive($status);
        $this->save($this->_em, $service);
    }

    public function removeProfessionals(Service $service): void
    {
        foreach ($service->getProfessionalRelations() as $professionalRelationship) {
            $this->delete($this->_em, $professionalRelationship);
        }
    }

    // /**
    //  * @return Service[] Returns an array of Service objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Service
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
