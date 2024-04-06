<?php


namespace App\Shared\Traits;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Service\FilterService;
use Studio128k\FilterService\Facades\FilterServiceFacade as Filter;

trait PaginatedRepositoryTrait
{
    public function paginateQueryBuilderResults(QueryBuilder $query, FilterService &$filterService, $fetchJoinCollection = true, $hydrationMode = Query::HYDRATE_OBJECT): array
    {
        $query = $query->getQuery()->setHydrationMode($hydrationMode);
        $query->setMaxResults($filterService->limit);
        // Pagination process
        $paginator = new Paginator($query, $fetchJoinCollection = true);

        $totalRegisters = $paginator->count();
        $result         = [];
        foreach ($paginator as $entity) {
            $result[] = $entity;
        }

        $lastPage = (integer)ceil($totalRegisters / $filterService->limit);

        //FilterService::addTotalRegisters($totalRegisters);
        //Filter::addLastPage($lastPage);

        return $result;
    }
}
