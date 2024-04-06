<?php

namespace App\Repository;

use App\Entity\Config\Config;
use App\Entity\Config\ConfigType;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Config::class);
    }

    // /**
    //  * @return Config[] Returns an array of Config objects
    //  */

    public function findAllConfigs(?bool $hydrateObject = false)
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult($hydrateObject?Query::HYDRATE_OBJECT:Query::HYDRATE_ARRAY);
        ;
    }

    public function findAllModules(?bool $hydrateObject = false)
    {
        return $this->createQueryBuilder('c')
            ->where('c.tag IN (:tags)')
            ->setParameter('tags', ConfigType::MODULES)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult($hydrateObject?Query::HYDRATE_OBJECT:Query::HYDRATE_ARRAY);

    }

    public function findByTags(array $tags)
    {
        return $this->createQueryBuilder('c')
            ->where('c.tag IN (:tags)')
            ->setParameter('tags', $tags)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

    }

    public function createConfig
    (
        string $name,
        string $tag,
        ?string $description,
        ?string $value
    )
    {
        $config = (new Config())
            ->setName($name)
            ->setTag($tag)
            ->setDescription($description)
            ->setValue($value);

        $this->save($this->_em, $config);
    }

    public function updateConfig
    (
        Config $config,
        string $name,
        string $tag,
        ?string $description,
        ?string $value
    )
    {
        $config
            ->setName($name)
            ->setTag($tag)
            ->setDescription($description)
            ->setValue($value);

        $this->save($this->_em, $config);
    }


    public function persist(Config $config)
    {
        $this->_em->persist($config);
        $this->_em->flush();
    }


    /*
    public function findOneBySomeField($value): ?Config
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
