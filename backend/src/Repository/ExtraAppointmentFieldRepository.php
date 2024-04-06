<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Client;
use App\Entity\ExtraAppointmentField;
use App\Entity\User;
use App\Service\FilterService;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExtraAppointmentField|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExtraAppointmentField|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExtraAppointmentField[]    findAll()
 * @method ExtraAppointmentField[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExtraAppointmentFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtraAppointmentField::class);
    }

    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return ExtraAppointmentField[] Returns an array of User objects
     */

    public function findExtraAppointmentFields(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('eaf')
            ->join('eaf.client', 'client')
            ->addSelect('client')
            ->leftJoin('eaf.appointment', 'appointment')
            ->addSelect('appointment')
            ->leftJoin('eaf.options', 'options')
            ->addSelect('options')
        ;

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {
            if($filterService->getFilterValue('client') != null && $filterService->getFilterValue('client') != ''){
                $query->andWhere('client.id = :client')
                    ->setParameter('client', $filterService->getFilterValue('client'));
            }

            if($filterService->getFilterValue('appointment') != null && $filterService->getFilterValue('appointment') != ''){
                $query->andWhere('appointment.id = :appointment')
                    ->setParameter('appointment', $filterService->getFilterValue('appointment'));
            }

        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "title":
                        $query->orderBy('eaf.title', $order['order']);
                        break;
                    case "value":
                        $query->orderBy('eaf.value', $order['order']);
                        break;
                    case "type":
                        $query->orderBy('eaf.type', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('eaf.id', 'DESC');
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

    /**
    * @return ExtraAppointmentField[] Returns an array of ExtraAppointmentField objects
    */

    public function findExtraAppointmentFieldById(int $id)
    {
        return $this->createQueryBuilder('eaf')
            ->leftJoin('eaf.appointment', 'appointment')
            ->addSelect('appointment')
            ->leftJoin('eaf.client', 'client')
            ->addSelect('client')
            ->andWhere('eaf.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult(QUERY::HYDRATE_ARRAY)
        ;
    }

    public function findAllInfoById(int $id){
        return $this->createQueryBuilder('eaf')
            ->leftJoin('eaf.appointment', 'appointment')
            ->addSelect('appointment')
            ->leftJoin('eaf.client', 'client')
            ->addSelect('client')
            ->andWhere('eaf.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllInfoByClientIdTypesAndAppointmentId(string $appointmentId, ?array $types, DateTime $dateFrom, DateTime $dateTo){
        $query = $this->createQueryBuilder('eaf')
            ->leftJoin('eaf.appointment', 'appointment')
            ->addSelect('appointment')
            ->leftJoin('eaf.client', 'client')
            ->addSelect('client')
            ->andWhere('client.id LIKE :client')
            ->andWhere('eaf.created_at BETWEEN :date_from AND :date_to')
            ->setParameter('date_from', $dateFrom)
            ->setParameter('date_to', $dateTo)
        ;

        if($types){

            $query->andWhere('eaf.title IN (:types)')
                ->setParameter('types', $types);
        }

        $query->andWhere('appointment.id LIKE :appointment')
            ->setParameter('appointment', $appointmentId);


        $query->orderBy('eaf.created_at', 'ASC');

        return $query->getQuery()
            ->getResult();
    }

    public function createExtraAppointmentField(
        User $user,
        Appointment $appointment,
        string $type,
        string $title,
        string $value
    ): ExtraAppointmentField
    {
        $extraAppointmentField = (new ExtraAppointmentField())
            ->setAppointment($appointment)
            ->setUser($user)
            ->setType($type)
            ->setTitle($title)
            ->setValue($value)
            ;

        $this->_em->persist($extraAppointmentField);
        $this->_em->flush();

        return $extraAppointmentField;
    }

    public function editExtraAppointmentFieldValue(
        ExtraAppointmentField $extraAppointmentField,
        string $value
    ): ExtraAppointmentField
    {
        $extraAppointmentField
            ->setValue($value)
        ;

        $this->_em->persist($extraAppointmentField);
        $this->_em->flush();

        return $extraAppointmentField;
    }

    public function deleteExtraAppointmentField(
        ExtraAppointmentField $extraAppointmentField
    )
    {
        $this->_em->remove($extraAppointmentField);
        $this->_em->flush();
    }
}
