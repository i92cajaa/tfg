<?php

namespace App\Repository;

use App\Entity\Template\TemplateLine;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TemplateLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateLine[]    findAll()
 * @method TemplateLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateLineRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateLine::class);
    }

    public function persist(TemplateLine $templateLine)
    {
        $this->save($this->_em, $templateLine);
    }

    public function remove(TemplateLine $templateLine)
    {
        $this->delete($this->_em, $templateLine);
    }

    // /**
    //  * @return TemplateLine[] Returns an array of TemplateLine objects
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
    public function findOneBySomeField($value): ?TemplateLine
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
