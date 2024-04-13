<?php

namespace App\Repository;

use App\Entity\Center\Center;
use App\Service\FilterService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Center>
 *
 * @method Center|null find($id, $lockMode = null, $lockVersion = null)
 * @method Center|null findOneBy(array $criteria, array $orderBy = null)
 * @method Center[]    findAll()
 * @method Center[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CenterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Center::class);
    }

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CREATE A NEW CENTER
     * ES: FUNCIÓN PARA CREAR UN CENTRO NUEVO
     *
     * @param Center $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function save(Center $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO DELETE A CENTER
     * ES: FUNCIÓN PARA BORRAR UN CENTRO
     *
     * @param Center $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function remove(Center $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A CENTER'S DATA
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UN CENTRO
     *
     * @param string $id
     * @param bool $array
     * @return array|Center|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findById(string $id, bool $array): array|Center|null
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.logo', 'logo')
            ->leftJoin('c.users', 'users')
            ->leftJoin('c.area', 'area')
            ->leftJoin('c.lessons', 'lessons')
            ->addSelect('logo')
            ->addSelect('users')
            ->addSelect('area')
            ->addSelect('lessons')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO LIST ALL CENTERS
     * ES: FUNCIÓN PARA LISTAR TODOS LOS CENTROS
     *
     * @param FilterService $filterService
     * @param bool $showAll
     * @return array|null
     */
    // ----------------------------------------------------------------
    public function findCenters(FilterService $filterService, $showAll = false): ?array
    {

        $query = $this->createQueryBuilder('i')
        ;

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        if(!$showAll){
            $query->setMaxResults($filterService->limit);
        }


        if (count($filterService->getFilters()) > 0) {

            if($filterService->getFilterValue('info') != null){
                $query->andWhere('CONCAT(i.name, i.address, i.city, i.phone) LIKE :info')
                    ->setParameter('info', "%".$filterService->getFilterValue('info')."%");
            }

        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('i.name', $order['order']);
                        break;
                    case "phone":
                        $query->orderBy('i.phone', $order['order']);
                        break;
                    case "city":
                        $query->orderBy('i.city', $order['order']);
                        break;
                    case "address":
                        $query->orderBy('i.address', $order['order']);
                        break;
                    case "director":
                        $query->orderBy('i.director', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('i.id', 'DESC');
        }

        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        if($filterService->limit){
            $lastPage = (integer)ceil($totalRegisters / $filterService->limit);
        }else{
            $lastPage = 1;
        }


        return [
            'totalRegisters' => $totalRegisters,
            'centers'        => $result,
            'lastPage'       => $lastPage
        ];
    }
    // ----------------------------------------------------------------
}
