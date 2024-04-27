<?php

namespace App\Repository;

use App\Entity\User\User;
use App\Entity\User\UserHasArea;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method UserHasArea|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserHasArea|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserHasArea[]    findAll()
 * @method UserHasArea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserHasAreaRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserHasArea::class);
    }

}