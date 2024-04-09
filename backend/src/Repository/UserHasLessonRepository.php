<?php

namespace App\Repository;

use App\Entity\User\UserHasLesson;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserHasLesson|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserHasLesson|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserHasLesson[]    findAll()
 * @method UserHasLesson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserHasLessonRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserHasLesson::class);
    }
}