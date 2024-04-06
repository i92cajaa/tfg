<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\User;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Utility\IdentifierFlattener;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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

    public function findOneByEmail(string $email)
    {
        $query = $this->createQueryBuilder('c')
            ->addSelect('c')
            ->andWhere('c.email LIKE :email')
            ->setParameter('email', $email)
            ;

        try {
            return $query->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return $query->getQuery()->getResult()[0];
        }
    }

    /**
     * @param FilterService $filterService
     * @param bool $showAll
     * @return User[] Returns an array of User objects
     */
    public function findClients(FilterService $filterService, $user, $isAdmin, $showAll = false)
    {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.center', 'center')
            ->leftJoin('p.users', 'users')
            ->leftJoin('users.user', 'user')
            ->addSelect('users')
            ->addSelect('user');



        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        //$query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {

            if($filterService->getFilterValue('info')!= ""){
                $query->andWhere("CONCAT(p.name, ' ', COALESCE(p.phone, ''), ' ', COALESCE(p.representative, ''), ' ', COALESCE(p.email, '')) LIKE :info")
                    ->setParameter('info', "%" . $filterService->getFilterValue('info') . "%");
            }

            if($filterService->getFilterValue('status') != null && $filterService->getFilterValue('status') != ''){
                    $query->andWhere('p.status = :status')
                    ->setParameter('status', $filterService->getFilterValue('status'));
            }

            if($filterService->getFilterValue('status_or_alumni') != null && $filterService->getFilterValue('status_or_alumni') != ''){
                if ($filterService->getFilterValue('status_or_alumni') === '2') {
                    $query->andWhere('p.alumni = 1');
                } else {
                    $query->andWhere('p.status = :status')
                        ->setParameter('status', $filterService->getFilterValue('status_or_alumni'))
                        ->andWhere('p.alumni = 0');
                }
            }
            
            if($filterService->getFilterValue('center') != null && $filterService->getFilterValue('center') != ''){
                $query->andWhere('p.center = :center')
                    ->setParameter('center', $filterService->getFilterValue('center'));
            }

        }

        if(!$isAdmin)
        {
            $query->andWhere('user.id = :user')
                ->setParameter(':user', $user);
        }



        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('p.name', $order['order']);
                        break;
                    case "center":
                        $query->orderBy('center.name', $order['order']);
                        break;
                    case "surnames":
                        $query->orderBy('p.surnames', $order['order']);
                        break;
                    case "dni":
                        $query->orderBy('p.dni', $order['order']);
                        break;
                    case "phone1":
                        $query->orderBy('p.phone1', $order['order']);
                        break;
                    case "announcementName":
                        $query->orderBy('p.announcement', $order['order']);
                        break;
                    case "phone2":
                        $query->orderBy('p.phone2', $order['order']);
                        break;
                    case "email":
                        $query->orderBy('p.email', $order['order']);
                        break;
                    case "address":
                        $query->orderBy('p.address', $order['order']);
                        break;
                    case "town":
                        $query->orderBy('p.town', $order['order']);
                        break;
                    case "province":
                        $query->orderBy('p.province', $order['order']);
                        break;
                    case "comments":
                        $query->orderBy('p.comments', $order['order']);
                        break;
                    case "status":
                        $query->orderBy('p.status', $order['order']);
                        break;

                }
            }
        } else {
            $query->orderBy('p.id', 'DESC');
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
        //$users = $query->getQuery()->getResult();
        return [
            'totalRegisters' => $totalRegisters,
            'data'           => $result,
            'lastPage'       => $lastPage
        ];
    }

    public function changeStatus(Client $client, bool $status){
        $client->setStatus($status);
        $this->save($this->_em, $client);
    }


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

        $this->persist($client);
    }

    /**
     * @param \DateTime $dateTimeFrom
     * @param \DateTime $dateTimeTo
     * @return Client[] Returns an array of Client objects
     */
    public function getCount(\DateTime $dateTimeFrom, \DateTime $dateTimeTo): array
    {
        $clientsArray = [];
        $dateTimeTo->setTime(0,0);
        $dateTimeToLoop1 = clone $dateTimeTo;
        $dateTimeFrom->setTime(0,0);
        $diff = $dateTimeFrom->diff($dateTimeTo)->days;

        for ($i=0; $i < $diff; $i++){

            $query =  $this->createQueryBuilder('p')
                ->select('count(p.id)')
                ->where('p.createdAt < :val')
                ->setParameter('val', $dateTimeToLoop1);

            $clients = $query->getQuery()
                ->getResult(Query::HYDRATE_SINGLE_SCALAR);

            $clientsArray[] = $clients;
            $dateTimeToLoop1->modify('-1 day');
        }


        return array_reverse($clientsArray);
    }


    public function persist(Client $client)
    {
        $this->save($this->_em, $client);
    }

    public function remove(Client $client)
    {
        $this->delete($this->_em, $client);
    }

}
