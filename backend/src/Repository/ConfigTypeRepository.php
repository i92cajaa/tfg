<?php

namespace App\Repository;

use App\Entity\Config;
use App\Entity\Config\ConfigType;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ConfigType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfigType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfigType[]    findAll()
 * @method ConfigType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigTypeRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfigType::class);
    }

    public function findAllOrdered(){
        return $this->createQueryBuilder('ct')
            ->addSelect('ct')
            ->orderBy('ct.order', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Config[] Returns an array of Config objects
    //  */



}
