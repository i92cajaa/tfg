<?php

namespace App\Repository;

use App\Entity\Payment\PaymentMethod;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentMethod[]    findAll()
 * @method PaymentMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentMethodRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentMethod::class);
    }

    // /**
    //  * @return PaymentMethod[] Returns an array of PaymentMethod objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaymentMethod
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return PaymentMethod[] Returns an array of Festive objects
     */
    public function findPaymentMethods(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('p');

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);


        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('p.name', $order['order']);
                        break;
                    case "description":
                        $query->orderBy('p.description', $order['order']);
                        break;
                    case "created_at":
                        $query->orderBy('p.created_at', $order['order']);
                        break;

                }
            }
        } else {
            $query->orderBy('p.id', 'DESC');
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

    public function persist(PaymentMethod $payment)
    {
        $this->save($this->_em, $payment);
    }

    public function remove(PaymentMethod $payment)
    {
        $this->delete($this->_em, $payment);
    }

    public function activatePaymentMethodsByIds(array $ids){
        $sql = "UPDATE payment SET active = 0";

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();

        $sql = "UPDATE payment SET active = 1 WHERE id IN(:ids)";

        $idsString = implode(',', $ids);
        $query = $this->_em->getConnection()->prepare($sql);
        $query->bindParam('ids', $idsString);
        $query->execute();
    }
}
