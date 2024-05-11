<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\User;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Utility\IdentifierFlattener;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use function Symfony\Component\String\s;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry,
                                private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
        parent::__construct($registry, Client::class);
    }

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CREATE A NEW CLIENT
     * ES: FUNCIÓN PARA CREAR UN CLIENTE NUEVO
     *
     * @param Client $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function save(Client $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO DELETE A CLIENT
     * ES: FUNCIÓN PARA BORRAR UN CLIENTE
     *
     * @param Client $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function remove(Client $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A CLIENT'S DATA
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UN CLIENTE
     *
     * @param string $id
     * @param bool $array
     * @return array|Client|null
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findById(string $id, bool $array): array|Client|null
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.bookings', 'bookings')
            ->leftJoin('bookings.schedule', 'schedule')
            ->leftJoin('c.notifications', 'notifications')
            ->addSelect('bookings')
            ->addSelect('schedule')
            ->addSelect('notifications')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO GET A CLIENT'S DATA (SIMPLE METHOD)
     * ES: FUNCIÓN PARA OBTENER LOS DATOS DE UN CLIENTE (MÉTODO SIMPLE)
     *
     * @param string $id
     * @param bool $array
     * @return array|Client
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function findByIdSimple(string $id, bool $array): array|Client
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult($array ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO LIST ALL CLIENTS
     * ES: FUNCIÓN PARA LISTAR TODOS LOS CLIENTES
     *
     * @param FilterService $filterService
     * @param bool $showAll
     * @param bool $array
     * @return array|null
     */
    // ----------------------------------------------------------------
    public function findClients(FilterService $filterService, bool $showAll, bool $array = false): ?array
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.bookings', 'bookings')
            ->leftJoin('bookings.schedule', 'schedule')
            ->leftJoin('c.notifications', 'notifications')
            ->addSelect('bookings')
            ->addSelect('schedule')
            ->addSelect('notifications')
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
            'clients'           => $result,
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
                    $conditions[] = 'c.name LIKE :' . $param . ' OR c.surnames LIKE :' . $param . ' OR CONCAT(c.name, c.surnames) LIKE :' . $param . ' OR c.phone LIKE :' . $param;
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

            $status = $filterService->getFilterValue('status');
            if ($status !== null && $status !== 'Todos') {
                $query->andWhere('c.status = :status')
                    ->setParameter('status', $status);
            }

            $dni = $filterService->getFilterValue('dni');
            if ($dni !== null) {
                $query->andWhere('c.dni LIKE :dni')
                    ->setParameter('dni', '%' . $dni . '%');
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
                        $query->orderBy('c.id', $order['order']);
                        break;
                    case "name":
                        $query->orderBy('CONCAT(c.name, c.surnames)', $order['order']);
                        break;
                    case "dni":
                        $query->orderBy('c.dni', $order['order']);
                        break;
                    case "phone":
                        $query->orderBy('c.phone', $order['order']);
                        break;
                    case "email":
                        $query->orderBy('c.email', $order['order']);
                        break;
                }
            }
        }
        else
        {
            $query->orderBy('c.createdAt', 'DESC');
        }
    }
    // --------------------------------------------------------------

    /**
     * Used to upgrade (rehash) the client's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $client, string $newHashedPassword): void
    {
        if (!$client instanceof Client) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($client)));
        }
        $newHashedPassword = $this->userPasswordHasher->hashPassword($client, $newHashedPassword);
        $client->setPassword($newHashedPassword);

        $this->save($client, true);
    }
}
