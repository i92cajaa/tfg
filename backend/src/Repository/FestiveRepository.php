<?php

namespace App\Repository;

use App\Entity\Festive\Festive;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Festive|null find($id, $lockMode = null, $lockVersion = null)
 * @method Festive|null findOneBy(array $criteria, array $orderBy = null)
 * @method Festive[]    findAll()
 * @method Festive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FestiveRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Festive::class);
    }

    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return Festive[] Returns an array of Festive objects
     */
    public function findFestives(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('f')
            ->leftJoin('f.user', 'user')
            ->addSelect('user')
        ;

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {

            if($filterService->getFilterValue('user') != null){

                $query->andWhere('CONCAT(user.name, user.email, user.surnames) LIKE :user')
                    ->setParameter('user', "%".$filterService->getFilterValue('user')."%");
            }

            if($filterService->getFilterValue('name') != null){
                $query->andWhere('f.name LIKE :name')
                    ->setParameter('name', "%".$filterService->getFilterValue('name')."%");
            }

            if($filterService->getFilterValue('dateRange') != null){

                $dates = explode(' ', $filterService->getFilterValue('dateRange'));

                $dateFrom = UTCDateTime::create('d-m-Y',$dates[0], new \DateTimeZone('UTC'))->setTime(0,0);
                $dateTo = UTCDateTime::create('d-m-Y',$dates[2], new \DateTimeZone('UTC'))->setTime(23,59);
                $query->andWhere('f.date BETWEEN :dateFrom AND :dateTo')
                    ->setParameter('dateFrom', $dateFrom)
                    ->setParameter('dateTo', $dateTo);
            }

        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('f.name', $order['order']);
                        break;
                    case "user":
                        $query->orderBy('user.name', $order['order']);
                        break;
                    case "date":
                        $query->orderBy('f.date', $order['order']);
                        break;

                }
            }
        } else {
            $query->orderBy('f.id', 'DESC');
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
     * @return Festive[] Returns an array of Festive objects
     */
    public function findAllUsersfestives()
    {
        return $this->createQueryBuilder('f')
            ->select('f.date')
            ->andWhere('f.user is NULL')
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    public function findByUserfestives($id)
    {
        return $this->createQueryBuilder('f')
            ->select('f.date')
            ->andWhere('f.user = :value')
            ->setParameter(':value', $id)
            ->orderBy('f.id', 'ASC')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    public function persist(Festive $festive)
    {
        $this->save($this->_em, $festive);
    }

    public function remove(Festive $festive)
    {
        $this->delete($this->_em, $festive);
    }


}
