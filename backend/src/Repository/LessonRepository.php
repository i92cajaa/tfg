<?php

namespace App\Repository;

use App\Entity\Lesson\Lesson;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lesson|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lesson|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lesson[]    findAll()
 * @method Lesson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LessonRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CREATE A NEW LESSON
     * ES: FUNCIÓN PARA CREAR UNA CLASE NUEVA
     *
     * @param Lesson $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function save(Lesson $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO DELETE A LESSON
     * ES: FUNCIÓN PARA BORRAR UNA CLASE
     *
     * @param Lesson $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function remove(Lesson $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A LESSON'S DATA
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UNA CLASE
     *
     * @param string $id
     * @param bool $array
     * @return array|Lesson|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findById(string $id, bool $array): array|Lesson|null
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.image', 'image')
            ->leftJoin('l.center', 'center')
            ->leftJoin('l.users', 'userHasLesson')
            ->leftJoin('userHasLesson.user', 'user')
            ->leftJoin('l.schedules', 'schedules')
            ->addSelect('image')
            ->addSelect('center')
            ->addSelect('userHasLesson')
            ->addSelect('user')
            ->addSelect('schedules')
            ->andWhere('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A LESSON'S DATA (SIMPLE METHOD)
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UNA CLASE (MÉTODO SIMPLE)
     *
     * @param string $id
     * @param bool $array
     * @return array|Lesson|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findByIdSimple(string $id, bool $array): array|Lesson|null
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO LIST ALL LESSONS
     * ES: FUNCIÓN PARA LISTAR TODAS LAS CLASES
     *
     * @param FilterService $filterService
     * @param bool $showAll
     * @param bool $array
     * @return array|null
     */
    // ----------------------------------------------------------------
    public function findLessons(FilterService $filterService, bool $showAll, bool $array = false): ?array
    {
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.image', 'image')
            ->leftJoin('l.center', 'center')
            ->leftJoin('l.users', 'userHasLesson')
            ->leftJoin('userHasLesson.user', 'user')
            ->leftJoin('l.schedules', 'schedules')
            ->addSelect('image')
            ->addSelect('center')
            ->addSelect('userHasLesson')
            ->addSelect('user')
            ->addSelect('schedules')
        ;

        $this->setFilters($query, $filterService);
        $this->setOrders($query, $filterService);

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);

        if(!$showAll){
            $query->setMaxResults($filterService->limit);
        }

        // Pagination process
        $paginator = new Paginator($query);
        $paginator->getQuery()->setHydrationMode($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
        $totalRegisters = $paginator->count();

        $result = [];

        foreach ($paginator as $verification) {
            $result[] = $verification;
        }

        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);

        return [
            'totalRegisters'    => $totalRegisters,
            'lessons'           => $result,
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
            $search_array = $filterService->getFilterValue('search_array');
            if ($search_array != null)
            {
                $array_values = explode(' ', $search_array);

                $conditions = [];
                $parameters = [];

                foreach ($array_values as $index => $value)
                {
                    $param = 'search' . $index;
                    $conditions[] = 'l.name LIKE :' . $param;
                    $parameters[$param] = '%' . $value . '%';
                }

                if (!empty($conditions))
                {
                    $query->andWhere(implode(' AND ', $conditions));

                    foreach($parameters as $key => $value)
                    {
                        $query->setParameter($key, $value);
                    }
                }
            }

            $center = $filterService->getFilterValue('center');
            if ($center !== null) {
                $query->andWhere('l.center = :center')
                    ->setParameter('center', $center);
            }

            $status = $filterService->getFilterValue('status');
            if ($status != null) {
                $query->andWhere('l.status = 1');
            } elseif ($status !== null) {
                $query->andWhere('l.status = 0');
            }

            $teacher = $filterService->getFilterValue('teacher');
            if ($teacher !== null) {
                $query->andWhere('user.id = :teacher')
                    ->setParameter('teacher', $teacher);
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
                        $query->orderBy('l.id', $order['order']);
                        break;
                    case "name":
                        $query->orderBy('l.name', $order['order']);
                        break;
                    case "center":
                        $query->orderBy('center.name', $order['order']);
                        break;
                    case "status":
                        $query->orderBy('l.status', $order['order']);
                        break;
                }
            }
        }
        else
        {
            $query->orderBy('l.name', 'DESC');
        }
    }
    // --------------------------------------------------------------
}