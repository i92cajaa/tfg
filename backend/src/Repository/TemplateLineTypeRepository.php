<?php

namespace App\Repository;

use App\Entity\Template\TemplateLineType;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TemplateLineType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateLineType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateLineType[]    findAll()
 * @method TemplateLineType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateLineTypeRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateLineType::class);
    }

    public function persist(TemplateLineType $templateLineType)
    {
        $this->save($this->_em, $templateLineType);
    }

    public function remove(TemplateLineType $templateLineType)
    {
        $this->delete($this->_em, $templateLineType);
    }

    // /**
    //  * @return TemplateLineType[] Returns an array of TemplateLineType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TemplateLineType
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
