<?php


namespace App\Repository;

use App\Entity\User\User;
use App\Entity\User\UserHasRole;
use App\Entity\User\UserHasService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method UserHasService|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserHasService|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserHasService[]    findAll()
 * @method UserHasService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserHasServiceRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserHasService::class);
    }



}