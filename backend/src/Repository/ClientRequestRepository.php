<?php

namespace App\Repository;

use App\Entity\ClientRequest\ClientRequest;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientRequest[]    findAll()
 * @method ClientRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRequestRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientRequest::class);
    }

    public function persist(ClientRequest $clientRequest)
    {
        $this->save($this->_em, $clientRequest);
    }

    public function remove(ClientRequest $clientRequest)
    {
        $this->delete($this->_em, $clientRequest);
    }

    public function findClientRequests(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.status', 'status')
            ->addSelect('status')
        ;

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {

            if($info = $filterService->getFilterValue('info') and $info != ""){
                $query->andWhere("CONCAT(r.name, ' ', r.surnames, ' ', COALESCE(r.phone, ''), ' ', COALESCE(r.email, '')) LIKE :info")
                    ->setParameter('info', "%".$info."%");
            }

            if($filterService->getFilterValue('status') != null && $filterService->getFilterValue('status') != ''){
                $query->andWhere('r.status = :status')
                    ->setParameter('status', $filterService->getFilterValue('status'));
            }

        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('r.name', $order['order']);
                        break;
                    case "surnames":
                        $query->orderBy('r.surnames', $order['order']);
                        break;
                    case "phone1":
                        $query->orderBy('r.phone', $order['order']);
                        break;
                    case "email":
                        $query->orderBy('r.email', $order['order']);
                        break;
                    case "comments":
                        $query->orderBy('r.comments', $order['order']);
                        break;
                    case "created_at":
                        $query->orderBy('r.createdAt', $order['order']);
                        break;
                    case "status":
                        $query->orderBy('status.id', $order['order']);
                        break;

                }
            }
        } else {
            $query->orderBy('r.id', 'DESC');
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
            'totalRegisters' => $totalRegisters,
            'data'           => $result,
            'lastPage'       => $lastPage
        ];
    }

    // /**
    //  * @return Request[] Returns an array of Request objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Request
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
