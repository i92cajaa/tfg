<?php

namespace App\Repository;

use App\Entity\Schedules\Schedules;
use App\Entity\User\User;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Schedules|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedules[]    findAll()
 * @method Schedules[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchedulesRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedules::class);
    }

    public function find($id, $lockMode = null, $lockVersion = null){


        return $this->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.user', 'user')
            ->addSelect( 'user')
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return User[] Returns an array of User objects
     */

    public function findSchedules(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('s')->join('s.user', 'user');

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {

            if($filterService->getFilterValue('user') != null){
                $query->andWhere('user.id LIKE :user')
                    ->setParameter('user', "%".$filterService->getFilterValue('user')[0]."%");
            }

            if($filterService->getFilterValue('timeFrom') != null){
                $timeFrom = UTCDateTime::create('H:i', $filterService->getFilterValue('timeFrom'), new \DateTimeZone('UTC'));
                $query->andWhere('s.timeFrom > :timeFrom')
                    ->setParameter('timeFrom', "%".$timeFrom."%");
            }

            if($filterService->getFilterValue('timeTo') != null){
                $timeTo = UTCDateTime::create('H:i', $filterService->getFilterValue('timeTo'), new \DateTimeZone('UTC'));
                $query->andWhere('s.timeTo < :timeTo')
                    ->setParameter('timeTo', "%".$timeTo."%");
            }

            if($filterService->getFilterValue('weekDay') != null){
                $query->andWhere('s.weekDay LIKE :weekDay')
                    ->setParameter('weekDay', "%".$filterService->getFilterValue('weekDay')[0]."%");
            }

            if($filterService->getFilterValue('status') != null){
                $query->andWhere('s.status = :status')
                    ->setParameter('status', $filterService->getFilterValue('status'));
            }else{
                $query->andWhere('s.status = 1');
            }

        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "user":
                        $query->orderBy('s.user', $order['order']);
                        break;
                    case "timeFrom":
                        $query->orderBy('s.timeFrom', $order['order']);
                        break;
                    case "timeTo":
                        $query->orderBy('s.timeTo', $order['order']);
                        break;
                    case "weekDay":
                        $query->orderBy('s.weekDay', $order['order']);
                        break;

                }
            }
        } else {
            $query->orderBy('s.user', 'DESC');
        }

        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);
        //$users = $query->getQuery()->getResult();

        return [
            'totalRegisters' => $totalRegisters,
            'data'           => $result,
            'lastPage'       => $lastPage
        ];
    }


    public function findByDays(): array
    {
        $schedules = [];
        for ($i = 0; $i < 7; $i++) {
            $schedulesDays = $this->createQueryBuilder('s')
                ->andWhere('s.status = 1')
                ->andWhere('s.weekDay = :val')
                ->setParameter('val', $i)
                ->getQuery()
                ->getResult()
            ;
            array_push($schedules, $schedulesDays);
        }

        return $schedules;

    }

    public function createSchedule(
        User $user,
        DateTime $timeFrom,
        DateTime $timeTo,
        int $weekDay,
        bool $status,
        ?bool $fixed
    ){
        $schedule = (new Schedules())
            ->setUser($user)
            ->setTimeFrom($timeFrom)
            ->setTimeTo($timeTo)
            ->setWeekDay($weekDay)
            ->setStatus($status)
            ->setFixed($fixed)
        ;

        $this->_em->persist($schedule);
        $this->_em->flush();
    }

    public function createAllWeekSchedules(User $user) {
        $weekDays = [1,2,3,4,5];

        $timeFrom = new \DateTime('09:00:00');
        $timeTo = new \DateTime('21:00:00');

        foreach ($weekDays as $weekDay) {
            $schedule = (new Schedules())
                ->setUser($user)
                ->setTimeFrom($timeFrom)
                ->setTimeTo($timeTo)
                ->setWeekDay($weekDay)
                ->setStatus(1)
                ->setFixed(false);

            $this->_em->persist($schedule);
            $this->_em->flush();
        }
    }


    public function toggleSchedule(
        Schedules $schedules,
        bool $status
    ){
        $schedules->setStatus($status);

        $this->_em->persist($schedules);
        $this->_em->flush();
    }

    public function findSchedulesByUserAndWeekDay(User $user, int $weekDay)
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.user', 'user')
            ->andWhere('s.status = 1')
            ->andWhere('user.id = :user')
            ->andWhere('s.weekDay = :weekDay')
            ->setParameter('user', $user->getId())
            ->setParameter('weekDay', $weekDay)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAvailableSchedulesByServiceIdsAndDate(string $serviceIds, string $date, string $userId){

        $start = UTCDateTime::create('Y-m-d',$date, new \DateTimeZone('UTC'))->setTime(0,0,0);

        return $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.user', 'user')
            //->join('user.services', 'uhs')
            //->join('uhs.service', 'service', 'WITH', 'service.id IN(:serviceIds)')
            //->leftJoin('user.festives', 'festive', 'WITH', 'festive.date != :date')
            ->addSelect('user')
            ->andWhere('user.id = :user')
            ->andWhere('s.status = 1')
            ->andWhere('s.weekDay = :weekDay')
            /*->andWhere("(
                    SELECT COUNT(s1.id) 
                    FROM App\Entity\Service\Service as s1 
                    LEFT JOIN s1.professionals as uhs1
                    LEFT JOIN uhs1.user as p1
                    WHERE s1.id IN(:serviceIds) AND p1.id = user.id
                ) = :servicesLength
            ")
            */
            //->setParameter('servicesLength', 1)
            //->setParameter('serviceIds', $serviceIds)
            ->setParameter('user', $userId)
            ->setParameter('weekDay', $start->format('w'))
            //->setParameter('date', $start)
            ->orderBy('s.timeFrom', 'ASC')
            ->getQuery()
            ->getArrayResult()
            ;
    }
    public function findAvailableSchedules(string $userId, string $date){

        $start = UTCDateTime::create('Y-m-d',$date, new \DateTimeZone('UTC'))->setTime(0,0,0);

        return $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.user', 'user')
            ->andWhere('s.status = 1')
            ->andWhere('user.id = :user')
            ->andWhere('s.weekDay = :weekDay')
            ->setParameter('user', $userId)
            ->setParameter('weekDay', $start->format('w'))
            ->orderBy('s.timeFrom', 'ASC')
            ->getQuery()
            ->getArrayResult()
            ;
    }

    public function findAvailableScheduleByUserAndDates(?User $user, DateTime $startDate, DateTime $endDate, string $weekday){
        $start = UTCDateTime::create('!d','01', new \DateTimeZone('UTC'))->setTime($startDate->format('H'), $startDate->format('i'));
        $end = UTCDateTime::create('!d','01', new \DateTimeZone('UTC'))->setTime($endDate->format('H'), $endDate->format('i'));

        $query =  $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.user', 'user')
            ->andWhere('s.status = 1')
            ->andWhere('s.weekDay = :weekDay')
            ->andWhere('(:timeFrom BETWEEN s.timeFrom AND s.timeTo) OR :timeFrom = s.timeFrom')
            ->andWhere('(:timeTo BETWEEN s.timeFrom AND s.timeTo) OR :timeTo = s.timeTo')
            ->setParameter('weekDay', $weekday)
            ->setParameter('timeFrom', $start->format('Y-m-d H:i:s'))
            ->setParameter('timeTo', $end->format('Y-m-d H:i:s'));

        if($user){
            $query->andWhere('user.id = :user')
                ->setParameter('user', $user->getId());
        }

        try {
            return $query
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return $query
                ->getQuery()
                ->getResult()[0];
        }
    }

    public function findWeekDaysByDatesAndUser(User $user, DateTime $timeFrom, DateTime $timeTo):array
    {
        return $this->createQueryBuilder('s')
            ->select('s')
            ->join('s.user', 'user')
            ->andWhere('s.status = 1')
            ->andWhere('user.id = :user')
            ->setParameter('user', $user->getId())
            ->andWhere('s.timeFrom = :timeFrom')
            ->setParameter('timeFrom', $timeFrom->format('Y-m-d H:i:s'))
            ->andWhere('s.timeTo = :timeTo')
            ->setParameter('timeTo', $timeTo->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult()
            ;
    }

    public function findAvailables($weekDay): Array
    {

        $schedules = $this->createQueryBuilder('s')
            ->join('s.user', 'user')
            ->addSelect('user')
            ->andWhere('s.status = 1')
            ->andWhere('s.weekDay = :val')
            ->setParameter('val', $weekDay)
            ->getQuery()
            ->getArrayResult()
        ;


        return $schedules;

    }

    public function persist(Schedules $schedules)
    {

        $this->save($this->_em, $schedules);

    }

    public function remove(Schedules $schedules)
    {

        $this->delete($this->_em, $schedules);

    }

}
