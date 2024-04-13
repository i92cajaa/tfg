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

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO CREATE A NEW USERHASLESSON
     * ES: FUNCIÓN PARA CREAR UN USERHASLESSON NUEVO
     *
     * @param UserHasLesson $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function save(UserHasLesson $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO DELETE AN USERHASLESSON
     * ES: FUNCIÓN PARA BORRAR UN USERHASLESSON
     *
     * @param UserHasLesson $entity
     * @param bool $flush
     * @return void
     */
    // ----------------------------------------------------------------
    public function remove(UserHasLesson $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // ----------------------------------------------------------------
}