<?php


namespace App\Repository;

use App\Entity\User\User;
use App\Entity\User\UserHasClient;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method UserHasClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserHasClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserHasClient[]    findAll()
 * @method UserHasClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserHasClientRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserHasClient::class);
    }



}