<?php

namespace App\Repository;

use App\Entity\ExtraAppointmentField;
use App\Entity\Template\TemplateLineType;
use App\Entity\Template\TemplateType;
use App\Service\FilterService;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TemplateType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateType[]    findAll()
 * @method TemplateType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateTypeRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TemplateType::class);
    }

    public function persist(TemplateType $templateType)
    {
        $this->save($this->_em, $templateType);
    }

    public function remove(TemplateType $templateType)
    {
        $this->delete($this->_em, $templateType);
    }

    public function findTemplateTypesByIds(array $ids)
    {
        return $this->createQueryBuilder('tt')
            ->leftJoin('tt.templateLineTypes', 'templateLineTypes')
            ->addSelect('templateLineTypes')
            ->where('tt.id IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTemplateTypes(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('tt')
            ->leftJoin('tt.templateLineTypes', 'templateLineTypes')
            ->addSelect('templateLineTypes')
        ;

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1)*$filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);

        if (count($filterService->getFilters()) > 0) {
            if($filterService->getFilterValue('name') != null && $filterService->getFilterValue('name') != ''){
                $query->andWhere('tt.id = :name')
                    ->setParameter('name', $filterService->getFilterValue('name'));
            }

            if($filterService->getFilterValue('entity') != null && $filterService->getFilterValue('entity') != ''){
                $query->andWhere('tt.entity = :entity')
                    ->setParameter('entity', $filterService->getFilterValue('entity'));
            }

        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "name":
                        $query->orderBy('tt.name', $order['order']);
                        break;
                    case "entity":
                        $query->orderBy('tt.entity', $order['order']);
                        break;
                    case "description":
                        $query->orderBy('tt.description', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('tt.id', 'DESC');
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

    public function deleteTemplateType(
        TemplateType $templateType
    )
    {
        $this->_em->remove($templateType);
        $this->_em->flush();
    }

    public function addTemplateLineTypes(TemplateType $templateType, array $templateLineTypes): TemplateType
    {
        foreach ($templateLineTypes as $templateLineType){

            $templateLineTypeObj = (new TemplateLineType())
                ->setName($templateLineType['name'])
                ->setType($templateLineType['type'])
                ->setOptions(@$templateLineType['options']?:[])
            ;

            $templateType->addTemplateLineType($templateLineTypeObj);
        }

        return $templateType;
    }

    public function removeAllTemplateLineTypes(TemplateType $templateType){
        foreach ($templateType->getTemplateLineTypes() as $templateLineType){
            $templateType->removeTemplateLineType($templateLineType);
        }

        $this->save($this->_em, $templateType);

        return $templateType;

    }

    // /**
    //  * @return TemplateType[] Returns an array of TemplateType objects
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
    public function findOneBySomeField($value): ?TemplateType
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
