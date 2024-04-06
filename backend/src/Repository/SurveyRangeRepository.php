<?php

namespace App\Repository;

use App\Entity\Document\Document;
use App\Entity\Document\SurveyRange;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use App\Service\FilterService;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\This;


class SurveyRangeRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyRange::class);
    }

    /**
     * @param null $startDate
     * @param null $endDate
     * @param bool $status
     * @return SurveyRange
     */
    public function createSurveyRange(
        $startDate = null,
        $endDate = null,
        $status = true
    ): SurveyRange
    {
        $surveyRange = (new SurveyRange())
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setStatus($status);

        $this->persist($surveyRange);

        return $surveyRange;
    }

    /**
     * @param string $surveyRangeId
     * @return SurveyRange|null
     */
    public function findSurveyRange(string $surveyRangeId): ?SurveyRange
    {
        return $this->find($surveyRangeId);
    }

    public function deleteSurveyRange(SurveyRange $surveyRange): void
    {
        $this->delete($this->_em, $surveyRange);
    }

    public function persist(SurveyRange $surveyRange)
    {
        $this->save($this->_em, $surveyRange);
    }

    public function findSurveyRanges(FilterService $filterService, $showAll = false)
    {

        $query = $this->createQueryBuilder('sr');

        if (count($filterService->getFilters()) > 0) {

            if ($filterService->getFilterValue('dateRange') != null) {

                $dates = explode(' ', $filterService->getFilterValue('dateRange'));
                if(sizeof($dates) > 1){
                    $timeFrom = UTCDateTime::create('d-m-Y', $dates[0])->setTime(0, 0);
                    $timeTo   = UTCDateTime::create('d-m-Y', $dates[2])->setTime(23, 59);
                    $query->andWhere('sr.startDate <= :timeFrom AND sr.endDate > :timeFrom')
                        ->orWhere('sr.startDate <= :timeTo AND sr.endDate > :timeTo')
                        ->setParameter('timeFrom', $timeFrom)
                        ->setParameter('timeTo', $timeTo);
                }else{
                    $timeFrom = UTCDateTime::create('d-m-Y', $dates[0])->setTime(0, 0);
                    $timeTo   = UTCDateTime::create('d-m-Y', $dates[0])->setTime(23, 59);
                    $query->andWhere('sr.startDate <= :timeFrom AND sr.endDate > :timeFrom')
                        ->orWhere('sr.startDate <= :timeTo AND sr.endDate > :timeTo')
                        ->setParameter('timeFrom', $timeFrom)
                        ->setParameter('timeTo', $timeTo);
                };

            }

            if ($filterService->getFilterValue('status') !== null && $filterService->getFilterValue('status') !== '') {
                $query->andWhere('sr.status = :status')
                    ->setParameter('status', $filterService->getFilterValue('status'));
            }

            if ($filterService->getFilterValue('startDate') != null) {
                $dateFrom = UTCDateTime::create('d-m-Y', $filterService->getFilterValue('startDate'))->setTime(23, 59, 59);
                $query->andWhere('sr.startDate > :dateFrom')
                    ->setParameter('dateFrom', $dateFrom);
            }

            if ($filterService->getFilterValue('endDate') != null) {
                $dateTo = UTCDateTime::create('d-m-Y', $filterService->getFilterValue('endDate'))->setTime(23, 59, 59);
                $query->andWhere('sr.endDate < :dateTo')
                    ->setParameter('dateTo', $dateTo);
            }

        }

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "status":
                        $query->orderBy('sr.status', $order['order']);
                        break;
                    case "startDate":
                        $query->orderBy('sr.startDate', $order['order']);
                        break;
                    case "endDate":
                        $query->orderBy('sr.endDate', $order['order']);
                        break;
                }
            }
        } else {
            $query->orderBy('sr.startDate', 'ASC');
        }

        if(!$showAll){
            $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1) * $filterService->limit) : $filterService->page - 1);
            $query->setMaxResults($filterService->limit);
        }


        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $verification) {
            $result[] = $verification;
        }


        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);


        return [
            'totalRegisters'       => $totalRegisters,
            'data'                 => $result,
            'lastPage'             => $lastPage
        ];
    }
}
