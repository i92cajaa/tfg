<?php

namespace App\Repository;


use App\Entity\ExtraAppointmentField\ExtraAppointmentFieldType;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExtraAppointmentFieldType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExtraAppointmentFieldType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExtraAppointmentFieldType[]    findAll()
 * @method ExtraAppointmentFieldType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExtraAppointmentFieldTypeRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtraAppointmentFieldType::class);
    }

    public function findExtraAppointmentFieldTypesByIds(array $ids){
        return $this->createQueryBuilder('eaft')
            ->where('eaft.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->getResult();
        ;
    }

    // /**
    //  * @return ExtraAppointmentFieldType[] Returns an array of ExtraAppointmentFieldType objects
    //  */

    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return ExtraAppointmentFieldType[] Returns an array of Festive objects
     */
    public function findExtraAppointmentFieldTypes(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('eaft')
            ->leftJoin('eaft.division', 'division')
            ->addSelect('division')
        ;

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {

            if ($filterService->getFilterValue('name') != null) {
                $query->andWhere('eaft.name LIKE :name')
                    ->setParameter('name', $filterService->getFilterValue('name'));

            }

            if ($filterService->getFilterValue('type') != null) {
                $query->andWhere('eaft.type LIKE :type')
                    ->setParameter('type', $filterService->getFilterValue('type'));

            }
        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('eaft.name', $order['order']);
                        break;
                    case "description":
                        $query->orderBy('eaft.description', $order['order']);
                        break;
                    case "type":
                        $query->orderBy('eaft.type', $order['order']);
                        break;

                    case "division":
                        $query->orderBy('division.name', $order['order']);
                        break;

                }
            }
        } else {
            $query->orderBy('eaft.id', 'DESC');
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

    public function findAllExtraAppointmentFieldTypes(?bool $hydrateObject = false)
    {
        return $this->createQueryBuilder('eaft')
            ->orderBy('eaft.id', 'ASC')
            ->getQuery()
            ->getResult($hydrateObject?Query::HYDRATE_OBJECT:Query::HYDRATE_ARRAY);
        ;
    }


    public function createExtraAppointmentFieldType
    (
        string $name,
        string $type,
        ?string $position,
        ?string $description
    )
    {
        $extraAppointmentFieldType = (new ExtraAppointmentFieldType())
            ->setName($name)
            ->setType($type)
            ->setPosition($position)
            ->setDescription($description);

        $this->save($this->_em, $extraAppointmentFieldType);
    }

    public function updateExtraAppointmentFieldType
    (
        ExtraAppointmentFieldType $extraAppointmentFieldType,
        string $name,
        string $type,
        ?string $position,
        ?string $description
    )
    {
        $extraAppointmentFieldType
            ->setName($name)
            ->setType($type)
            ->setPosition($position)
            ->setDescription($description);

        $this->save($this->_em, $extraAppointmentFieldType);
    }

    public function deleteExtraAppointmentFieldType(ExtraAppointmentFieldType $extraAppointmentFieldType){
        $this->_em->remove($extraAppointmentFieldType);
        $this->_em->flush();
    }


    public function persist(ExtraAppointmentFieldType $extraAppointmentFieldType)
    {
        $this->_em->persist($extraAppointmentFieldType);
        $this->_em->flush();
    }


    /*
    public function findOneBySomeField($value): ?ExtraAppointmentFieldType
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
