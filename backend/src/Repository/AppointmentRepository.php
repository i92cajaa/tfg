<?php

namespace App\Repository;

use App\Entity\Appointment\Appointment;
use App\Entity\Appointment\AppointmentHasService;
use App\Entity\Client\Client;
use App\Entity\Config\Config;
use App\Entity\Config\ConfigType;
use App\Entity\Schedules\Schedules;
use App\Entity\Status\Status;
use App\Entity\User\User;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;



/**
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointment[]    findAll()
 * @method Appointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findByEventId(string $groupId)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.users', 'user')
            ->addSelect('user')
            ->join('a.client', 'client')
            ->addSelect('client')
            ->join('a.schedule', 'schedules')
            ->addSelect('schedules')
            ->join('a.event', 'event')
            ->addSelect('event')
            ->leftJoin('a.services', 'ahs')
            ->addSelect('ahs')
            ->leftJoin('ahs.service', 'services')
            ->addSelect('services')
            ->leftJoin('services.division', 'division')
            ->addSelect('division')
            ->leftJoin('a.statusType', 'statusType')
            ->addSelect('statusType')
            ->andWhere('event.id = :event_id')
            ->setParameter('event_id', $groupId)
            ->getQuery()
            ->getResult()
        ;
    }

  

    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return Appointment[] Returns an array of User objects
     */

    public function findAppointments(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('a')
                      ->leftJoin('a.users', 'user')
                      ->addSelect('user')
                      ->leftJoin('a.center','centers')
                      ->addSelect('centers')
//                      ->leftJoin('user.center','user_center')
                      ->leftJoin('a.client', 'client')
                      ->addSelect('client')
                      ->leftJoin('client.center','center')
                      ->leftjoin('a.schedule', 'schedules')
                      ->addSelect('schedules')
                      ->leftJoin('a.services', 'ahs')
                      ->addSelect('ahs')
                      ->leftJoin('ahs.service', 'services')
                      ->addSelect('services')
                      ->leftJoin('services.division', 'division')
                      ->addSelect('division')
                      ->leftJoin('a.templates', 'template')
                      ->addSelect('template')
                      ->leftJoin('template.templateLines', 'template_line')
                      ->addSelect('template_line')
                      ->leftJoin('a.statusType', 'statusType')
                      ->addSelect('statusType')
                      ;

                    

        if (count($filterService->getFilters()) > 0) {

            if ($filterService->getFilterValue('user') != null) {
                if(is_array($filterService->getFilterValue('user')) && sizeof($filterService->getFilterValue('user')) > 0){
                    $query->andWhere('user.id LIKE :id')
                        ->setParameter('id', "%" . $filterService->getFilterValue('user')[0] . "%");
                }else{
                    $query->andWhere('user.id LIKE :id')
                        ->setParameter('id', "%" . $filterService->getFilterValue('user') . "%");
                }

            }
            if($filterService->getFilterValue('info') != ""){
                $query->andWhere("CONCAT(user.name, ' '
                                , COALESCE(user.surnames, ''), ' '
                                , COALESCE(user.email, ''), ' '
                                , COALESCE(client.name, ''), ' '
                                , COALESCE(client.phone, ''), ' '
                                , COALESCE(services.price, ''), ' '
                                , COALESCE(services.color, ''), ' ') LIKE :info")
                    ->setParameter('info', "%" . $filterService->getFilterValue('info') . "%");
            }

            if($filterService->getFilterValue('center') != null){
                $query->andWhere('centers.id IN (:center)')
                    ->setParameter('center', $filterService->getFilterValue('center'));
            }

            if (is_array($filterService->getFilterValue('appointment_ids')) && sizeof($filterService->getFilterValue('appointment_ids')) > 0) {
                $query->andWhere('a.id IN(:app_ids)')
                    ->setParameter('app_ids', $filterService->getFilterValue('appointment_ids'));
            }

            if ($filterService->getFilterValue('client') != null && $filterService->getFilterValue('client')[0] != '') {
                $query->andWhere('client.id LIKE :client')
                      ->setParameter('client', "%" . $filterService->getFilterValue('client')[0] . "%");
            }

            if ($users = $filterService->getFilterValue('users')) {
                $query->andWhere("user.id IN(:users)")
                    ->setParameter('users', $users);
            }

            if ($clients = $filterService->getFilterValue('clients')) {
                $query->andWhere("client.id IN(:clients)")
                    ->setParameter('clients', $clients);
            }


            if ($filterService->getFilterValue('infoUser') != null) {
                $query->andWhere('CONCAT(user.name, user.email, user.surnames) LIKE :infoUser')
                      ->setParameter('infoUser', "%" . $filterService->getFilterValue('infoUser') . "%");
            }

            if ($filterService->getFilterValue('infoPac') != null) {
                $query->andWhere('CONCAT(client.name, client.surnames, client.dni, client.phone1) LIKE :infoPac')
                      ->setParameter('infoPac', "%" . $filterService->getFilterValue('infoPac') . "%");
            }

            if ($filterService->getFilterValue('infoServ') != null) {
                $query->andWhere('CONCAT(services.name, services.price, services.color, services.type, division.name) LIKE :infoServ')
                      ->setParameter('infoServ', "%" . $filterService->getFilterValue('infoServ') . "%");
            }

            if ($filterService->getFilterValue('dateRange') != null) {

                $dates = explode(' ', $filterService->getFilterValue('dateRange'));
                if(sizeof($dates) > 1){
                    $timeFrom = UTCDateTime::create('d-m-Y', $dates[0])->setTime(0, 0);
                    $timeTo   = UTCDateTime::create('d-m-Y', $dates[2])->setTime(23, 59);
                    $query->andWhere('a.timeFrom BETWEEN :timeFrom AND :timeTo')
                        ->setParameter('timeFrom', $timeFrom)
                        ->setParameter('timeTo', $timeTo);
                }else{
                    $timeFrom = UTCDateTime::create('d-m-Y', $dates[0])->setTime(0, 0);
                    $timeTo   = UTCDateTime::create('d-m-Y', $dates[0])->setTime(23, 59);
                    $query->andWhere('a.timeFrom BETWEEN :timeFrom AND :timeTo')
                        ->setParameter('timeFrom', $timeFrom)
                        ->setParameter('timeTo', $timeTo);
                };

            }

            if($filterService->getFilterValue('services') != null){
                $query->andWhere('services.id IN (:services)')
                    ->setParameter('services', $filterService->getFilterValue('services'));
            }

            if ($divisions = $filterService->getFilterValue('divisions')) {
                $query->andWhere("division.id IN(:divisions)")
                      ->setParameter('divisions', $divisions);
            }

            if ($filterService->getFilterValue('week_day') != null && $filterService->getFilterValue('week_day')[0] != '') {
                $query->andWhere('schedules.week_day LIKE :week_day')
                      ->setParameter('week_day', "%" . $filterService->getFilterValue('week_day')[0] . "%");
            }
            if ($filterService->getFilterValue('area') != null && $filterService->getFilterValue('area') != '') {
                $query->andWhere('a.area = :area')
                    ->setParameter('area', $filterService->getFilterValue('area'));
            }

            if ($filterService->getFilterValue('statusType') != null && $filterService->getFilterValue('statusType') != '') {
                $query->andWhere('statusType.id = :statusType')
                      ->setParameter('statusType', $filterService->getFilterValue('statusType'));
            }

            if ($filterService->getFilterValue('paid') != null && $filterService->getFilterValue('paid')[0] != '') {
                $query->andWhere('a.paid LIKE :paid')
                    ->setParameter('paid', "%" . $filterService->getFilterValue('paid')[0] . "%");
            }

            if ($professional = $filterService->getFilterValue('professional')) {
                $query->andWhere('user.id = :professional')
                      ->setParameter('professional', $professional);
            }

            if ($professional = $filterService->getFilterValue('professional')) {
                $query->andWhere('user.id = :professional')
                      ->setParameter('professional', $professional);
            }


            if ($dateFrom = $filterService->getFilterValue('date_from')) {
                $dateFrom = UTCDateTime::create('d-m-Y', $dateFrom)->setTime(0, 0);
                $query->andWhere('a.timeFrom > :dateFrom')
                      ->setParameter('dateFrom', $dateFrom);
            }

            if ($dateTo = $filterService->getFilterValue('date_to')) {
                $dateTo = UTCDateTime::create('d-m-Y', $dateTo)->setTime(0, 0);
                $query->andWhere('a.timeFrom < :dateTo')
                    ->setParameter('dateTo', $dateTo);
            }

        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "user":
                        $query->orderBy('user.name', $order['order']);
                        break;
                    case "client":
                        $query->orderBy('client.name', $order['order']);
                        break;
                    case "status":
                        $query->orderBy('a.status', $order['order']);
                        break;
                    case "area":
                        $query->orderBy('a.area', $order['order']);
                        break;
                    case "date":
                        $query->orderBy('a.timeFrom', $order['order']);
                        break;
                    case "time_from":
                        $query->orderBy('schedules.timeFrom', $order['order']);
                        break;
                    case "time_to":
                        $query->orderBy('schedules.timeTo', $order['order']);
                        break;
                    case "totalPrice":
                        $query->orderBy('a.totalPrice', $order['order']);
                        break;
                    case "paid":
                        $query->orderBy('a.paid', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('a.timeFrom', 'DESC');
        }

        $appointments         = $query->getQuery()->getResult();
        $totalAmount          = 0;
        $totalAmountSecondary = 0;
        /** @var Appointment $appointment */
        foreach ($appointments as $appointment) {
            $totalAmount += $appointment->getTotalPrice();
        }

        if(!$showAll){
            $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1) * $filterService->limit) : $filterService->page - 1);
            $query->setMaxResults($filterService->limit);
        }


        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $verification) {
            $result[] = $verification;
        }


        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);


        return [
            'totalRegisters'       => $totalRegisters,
            'data'                 => $result,
            'lastPage'             => $lastPage,
            'totalAmount'          => $totalAmount
        ];
    }


    public function payAppointment(
        Appointment $appointment,
        bool $paid
    )
    {
        $appointment->setPaid($paid);

        $this->_em->persist($appointment);
        $this->_em->flush();
    }

    public function changeStatusType(
        Appointment $appointment,
        Status $status
    )
    {
        $appointment->setStatusType($status);

        $this->_em->persist($appointment);
        $this->_em->flush();
    }

    public function deleteAppointment(
        Appointment $appointment
    ){

        //$appointment->removeAllHistories();
        $this->_em->remove($appointment);

        $this->_em->flush();




    }

    public function createAppointment
    (
        User $user,
        Client $client,
        Schedules $schedules,
        array $services,
        bool $status,
        DateTime $timeFrom,
        DateTime $timeTo,
        ?Status $statusType,
        bool $paid,
        ?string $periodicId
    )
    {

        $appointment = (new Appointment())
            ->setUser($user)
            ->setClient($client)
            ->setSchedule($schedules)
            ->setStatus($status)
            ->setTimeFrom($timeFrom)
            ->setTimeTo($timeTo)
            ->setStatusType($statusType)
            ->setPaid($paid)
            ->setEmailSent(false)
            ->setPeriodicId($periodicId)
        ;

        foreach ($services as $service)
        {
            $appointment->addService($service);
        }
        $appointment->calculateTotalPrice();


        $this->_em->persist($appointment);
        $this->_em->flush();
    }

    public function findAppointmentsByIds(array $ids)
    {

        return $this->createQueryBuilder('a')
            ->join('a.users', 'user')
            ->addSelect('user')
            ->where('a.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getOneAppointmentByDatesAndSchedule(DateTime $start, Datetime $end, Schedules $schedule): ?Appointment
    {

        $query = $this->createQueryBuilder('a')
            ->join('a.schedule', 'schedules')
            ->addSelect('schedules')
            ->leftJoin('a.statusType', 'statusType')
            ->addSelect('statusType')
            ->andWhere('
            (
                (:timeFrom BETWEEN a.timeFrom AND a.timeTo AND :timeFrom != a.timeTo) OR (:timeFrom = a.timeFrom) 
            )
            OR
            (
                (:timeTo BETWEEN a.timeFrom AND a.timeTo AND :timeTo != a.timeFrom) OR (:timeTo = a.timeTo )
            )
            ')
            ->andWhere('schedules.id = :schedule ')
            ->setParameter('timeFrom', $start->format('Y-m-d H:i:s'))
            ->setParameter('timeTo', $end->format('Y-m-d H:i:s'))
            ->setParameter('schedule', $schedule->getId())
            ;

        try {
            return $query->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return $query->getQuery()->getResult()[0];
        }

    }

    public function getAppointmentsByDateAndUser(Datetime $start, Datetime $end, ?User $user)
    {

        $query = $this->createQueryBuilder('a')
            ->join('a.schedule', 'schedules')
            ->addSelect('schedules')
            ->leftJoin('a.statusType', 'statusType')
            ->join('a.users', 'user')
            ->addSelect('user')
            ->addSelect('statusType')
            ->andWhere('statusType.id != 1')
            ->andWhere('(a.timeFrom = :start AND a.timeTo = :end )')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'));

        if($user){
            $query->andWhere('user.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        return $query->getQuery()->getResult();
    }

    public function getAppointmentsBetweenDatesAndUser(Datetime $start, Datetime $end, ?User $user)
    {

        $query = $this->createQueryBuilder('a')
            ->join('a.schedule', 'schedules')
            ->addSelect('schedules')
            ->leftJoin('a.statusType', 'statusType')
            ->join('a.users', 'user')
            ->addSelect('user')
            ->addSelect('statusType')
            ->andWhere('statusType.id != 1')
            ->andWhere('(a.timeFrom BETWEEN :start AND :end ) OR (a.timeTo BETWEEN :start AND :end)')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'));

        if($user){
            $query->andWhere('user.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        return $query->getQuery()->getResult();
    }


    public function checkIfIsCompleted(string $schedules, string $date, DateTime $timeFrom, DateTime $timeTo, ?bool $fixed = false){
        if($fixed == null){
            $fixed = false;
        }
        $start = UTCDateTime::setUTC(UTCDateTime::create('Y-m-d',$date))->setTime($timeFrom->format('H'),$timeFrom->format('i'),$timeFrom->format('s'));
        $end = UTCDateTime::setUTC(UTCDateTime::create('Y-m-d',$date))->setTime($timeTo->format('H'),$timeTo->format('i'),$timeTo->format('s'));

        $query = $this->createQueryBuilder('a')
            ->join('a.schedule', 'schedules')
            ->addSelect('schedules')
            ->leftJoin('a.statusType', 'statusType')
            ->addSelect('statusType')
            ->andWhere('statusType.id != 1')
            ->andWhere('schedules = :schedule')
            ->andWhere('(a.timeFrom BETWEEN :start AND :end OR a.timeTo BETWEEN :start AND :end AND a.timeFrom != :end)')
            ->setParameter('schedule', $schedules)
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'))
        ;

        $appointments = $query->getQuery()->getResult();

        if(sizeof($appointments) == 0){
            return false;
        }elseif(sizeof($appointments) == 1 && $fixed == true){
            return true;
        }else{
            return false;
        }

    }


    /*
    ** @return Appointment[] Returns an array of Appointment objects
    */
    public function findAllInfo($user = null)
    {
        $query = $this->createQueryBuilder('a')
                      ->join('a.users', 'user')
                      ->addSelect('user')
                      ->join('a.client', 'client')
                      ->addSelect('client')
                      ->join('a.schedule', 'schedules')
                      ->addSelect('schedules')
                      ->andWhere('a.status = 1');


        if ($user != null) {
            $query
                ->andWhere('user.id = :user')
                ->setParameter('user', $user);
        }

        $query->orderBy('a.id', 'ASC');

        return $query->getQuery()->getResult();
    }

    /*
    ** @return Appointment[] Returns an array of Appointment objects
    */
    public function findByUser($user, ?DateTime $date = null)
    {
        $query = $this->createQueryBuilder('a')
                    ->join('a.schedule', 'schedule')
                    ->addSelect('schedule')
                    ->andWhere('a.users = :user')
                    ->setParameter('user', $user);
        if($date != null){
            $query->andWhere('a.timeFrom > :date')
                ->setParameter('date', $date);
        }
        $query->orderBy('a.timeFrom', 'ASC');

        return $query->getQuery()
            ->getArrayResult();
    }


    /**
     * @param DateTime $dateTimeFrom
     * @param DateTime $dateTimeTo
     * @return Appointment[] Returns an array of Client objects
     */
    public function getCount(\DateTime $dateTimeFrom, \DateTime $dateTimeTo): array
    {
        $appointmentsArray = [];

        $dateTimeTo->setTime(0, 0);
        $dateTimeToLoop = clone $dateTimeTo;
        $dateTimeFrom->setTime(0, 0);
        $diff = $dateTimeFrom->diff($dateTimeTo)->days;

        for ($i = 0; $i < $diff; $i++) {

            $appointments = $this->createQueryBuilder('a')
                                 ->select('count(a.id)')
                                 ->andWhere('a.timeFrom < :val')
                                 ->setParameter('val', $dateTimeToLoop)
                                 ->getQuery()
                                 ->getResult(Query::HYDRATE_SINGLE_SCALAR);

            array_push($appointmentsArray, $appointments);
            $dateTimeToLoop->modify('-1 days');
        }

        $appointmentsArray = array_reverse($appointmentsArray);
        return $appointmentsArray;
    }


    /**
     * @param DateTime $dateTimeFrom
     * @param DateTime $dateTimeTo
     * @return array
     */
    public function getTotalPrice(\DateTime $dateTimeFrom, \DateTime $dateTimeTo)
    {
        $appointmentAmountArray = [];


        $dateTimeTo->setTime(0, 0);
        $dateTimeFrom->setTime(0, 0);
        $diff = $dateTimeFrom->diff($dateTimeTo)->days;

        for ($i = 0; $i < $diff; $i++) {
            $totalAmount = 0;

            $appointments = $this->createQueryBuilder('a')
                                 ->select('a.totalPrice')
                                 ->andWhere('a.timeFrom < :val')
                                 ->setParameter('val', $dateTimeTo)
                                 ->getQuery()
                                 ->getResult();;

            foreach ($appointments as $appointment) {
                if ($appointment['totalPrice'] != null) {
                    $totalAmount += floatval($appointment['totalPrice']);
                }

            }
            array_push($appointmentAmountArray, $totalAmount);
            $dateTimeTo->modify('-1 days');
        }
        $appointmentAmountArray = array_reverse($appointmentAmountArray);
        return $appointmentAmountArray;
    }


    public function findByDate(int $days = 5)
    {
        $date = UTCDateTime::create('NOW');

        $startDate = UTCDateTime::create('NOW');
        $endDate   = $date->modify('+' . $days . ' days')->setTime(23, 0);

        return $this->createQueryBuilder('a')
                    ->join('a.client', 'client')
                    ->addSelect('client')
                    ->leftJoin('a.statusType', 'statusType')
                    ->addSelect('statusType')
                    ->andWhere('statusType.id != :statusTypeId')
                    ->andWhere('a.timeFrom BETWEEN :start AND :end')
                    ->setParameter('statusTypeId', Status::STATUS_ASSIGNED)
                    ->setParameter('start', $startDate)
                    ->setParameter('end', $endDate)
                    ->andWhere('a.emailSent = 0')
                    ->getQuery()
                    ->getResult();
    }


    public function findById($value): array
    {

        return $this->createQueryBuilder('a')
                    ->leftJoin('a.schedule', 'schedules')
                    ->addSelect('schedules')
                    ->leftJoin('a.services', 'ahs')
                    ->addSelect('ahs')
                    ->leftJoin('ahs.service', 'services')
                    ->addSelect('services')
                    ->leftJoin('a.client', 'client')
                    ->addSelect('client')
                    ->leftJoin('a.users', 'user')
                    ->addSelect('user')
                    ->andWhere('a.id = :id')
                    ->setParameter('id', $value)
                    ->getQuery()
                    ->getSingleResult(Query::HYDRATE_ARRAY);
    }

    public function findAppointmentById($value): ?Appointment
    {

        return $this->createQueryBuilder('a')
            ->leftJoin('a.schedule', 'schedules')
            ->addSelect('schedules')
            ->leftJoin('a.services', 'ahs')
            ->addSelect('ahs')
            ->leftJoin('ahs.service', 'services')
            ->addSelect('services')
            ->leftJoin('a.client', 'client')
            ->addSelect('client')
            ->leftJoin('a.users', 'user')
            ->addSelect('user')
            ->leftJoin('a.extraAppointmentFields', 'extraAppointmentFields')
            ->addSelect('extraAppointmentFields')
            ->andWhere('a.id = :id')
            ->setParameter('id', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function removeServices(Appointment $appointment):Appointment
    {
        foreach ($appointment->getAppointmentHasServices() as $ahs)
        {
            $this->_em->remove($ahs);
        }
        $this->_em->flush();

        return $this->findAppointmentById($appointment->getId());
    }


    public function findServices($value)
    {

        return $this->createQueryBuilder('a')
                    ->leftJoin('a.services', 'ahs')
                    ->addSelect('ahs')
                    ->leftJoin('ahs.service', 'services')
                    ->addSelect('services')
                    ->andWhere('a.id = :id')
                    ->setParameter('id', array($value))
                    ->getQuery()
                    ->getOneOrNullResult(Query::HYDRATE_ARRAY);
    }

    public function findByPeriodicIDAndDate(string $periodicId, DateTime $dateTime, string $operator){

        $query = $this->createQueryBuilder('a')
            ->select('a')
            ->where('a.periodicId = :periodicId')
            ->setParameter('periodicId', $periodicId);

        if($operator == 'next'){
            $datetime = UTCDateTime::create('d-m-Y', $dateTime->format('d-m-Y'), new \DateTimeZone('UTC'))->setTime(23, 59);
            $query->andWhere('a.timeFrom > :datetime');
        }else{
            $datetime = UTCDateTime::create('d-m-Y', $dateTime->format('d-m-Y'), new \DateTimeZone('UTC'))->setTime(0, 0);
            $query->andWhere('a.timeFrom < :datetime');
        }

        $query->setParameter('datetime', $datetime);

        return $query->getQuery()
        ->getResult();
    }

    public function findAppointmentByDuplicatedClient($dni, $clientId)
    {

        return $this->createQueryBuilder('a')
            ->leftJoin('a.client', 'client')
            ->addSelect('client')
            ->andWhere('client.dni LIKE :dni AND client.id != :clientId')
            ->setParameter('dni', "%$dni%")
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getResult();
    }


    public function modifyHour(
        Appointment $appointment,
        DateTime $timeFrom,
        DateTime $timeTo
    ){

        $appointment
            ->setTimeFrom($timeFrom)
            ->setTimeTo($timeTo)
        ;

        $this->save($this->_em, $appointment);

        return $appointment;
    }

    public function persist(Appointment $appointment)
    {

        $this->save($this->_em, $appointment);

        return $appointment;

    }



}
