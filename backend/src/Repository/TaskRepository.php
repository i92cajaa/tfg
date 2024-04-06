<?php


namespace App\Repository;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\Task\Task;
use App\Entity\Status\Status;
use App\Entity\User\User;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use App\Shared\Traits\PaginatedRepositoryTrait;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;
    use PaginatedRepositoryTrait;


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);

    }


    public function findTasks(FilterService $filterService, $showAll = false, $getStatus = false)
    {

        $query = $this->createQueryBuilder('task')
        ->leftJoin('task.user', 'user')
        ->leftJoin('task.statuses', 'statuses')
        ->leftJoin('task.currentStatus', 'currentTaskHasStatus')
        ->leftJoin('currentTaskHasStatus.status', 'currentStatus');

        $query->setFirstResult($filterService->page > 1 ? (($filterService->page - 1) * $filterService->limit) : $filterService->page - 1);
        $query->setMaxResults($filterService->limit);


        if (count($filterService->getFilters()) > 0) {

            if($filterService->getFilterValue('user') != null){
                $query->andWhere('user.id LIKE :user')
                    ->setParameter('user', $filterService->getFilterValue('user'));

            }

            if($filterService->getFilterValue('title') != null){
                $query->andWhere('task.title LIKE :title')
                    ->setParameter('title', "%".$filterService->getFilterValue('title')."%");
            }

            if($filterService->getFilterValue('date_from') != null){
                $query->andWhere('task.estimatedStartDate >= :date_from')
                    ->setParameter('date_from', $filterService->getFilterValue('date_from'));
            }

            if($filterService->getFilterValue('date_to') != null){
                $query->andWhere('task.estimatedStartDate <= :date_to')
                    ->setParameter('date_to', $filterService->getFilterValue('date_to'));
            }

            if($filterService->getFilterValue('status') != null){
                $query->andWhere('currentStatus.id = :status')
                    ->setParameter('status', intval($filterService->getFilterValue('status')));
            }
            /*if($filterService->getFilterValue('status_id') != null){
                //dd($filterService->getFilterValue('status_id'));
                $query->andWhere('currentStatus.status.id = :status_id')
                    ->setParameter('status_id', intval($filterService->getFilterValue('status_id')));
            }*/

        }
        $query->addGroupBy('task.estimatedStartDate');

        if (count($filterService->getOrders()) > 0) {
            foreach ($filterService->getOrders() as $order) {
                switch ($order['field']) {
                    case "title":
                        $query->orderBy('task.title', $order['order']);
                        break;

                    case "currentStatus":
                        $query->orderBy('currentStatus.name', $order['order']);
                        break;

                }
            }
        } else {
            $query->orderBy('task.id', 'DESC');
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
            'totalRegisters' => $totalRegisters,
            'data'           => $result,
            'lastPage'       => $lastPage
        ];
    }



    public function findTaskById($taskId): ?Task
    {
        $sql = $this->createQueryBuilder('task')
            ->select('task')
            ->leftJoin('task.user', 'user')
            ->addSelect('user')
            ->leftJoin('task.appointment', 'appointment')
            ->addSelect('appointment')
            ->leftJoin('task.client', 'client')
            ->addSelect('appointment')
            ->leftJoin('task.currentStatus', 'currentStatus')
            ->addSelect('currentStatus')
            ->leftJoin('task.statuses', 'statuses')
            ->addSelect('statuses')
            ->where('task.id = :id')
            ->setParameter('id', $taskId)
            ->getQuery();
        dd($sql->getSQL());


    }

    public function search(FilterService $filterService)
    {
        $query = $this->_em->getRepository(Task::class)
            ->createQueryBuilder('task')
            ->select('task')
            ->leftJoin('task.user', 'user')
            ->addSelect('user')
        ;

        $this->addFilters($query, $filterService);
        $this->addOrders($query, $filterService);

        return $this->paginateQueryBuilderResults($query, $filterService, true);
    }

    /*
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return $this->findTaskById($id);
    }
    */

    public function getTasksByIds(?array $taskIds): ?array
    {
        return $this->createQueryBuilder('task')
            ->select('task')
            ->where('task.id IN(:ids)')
            ->setParameter('ids', $taskIds)
            ->getQuery()
            ->getResult();
    }

    public function addFilters(QueryBuilder &$query, FilterService $filterService): void
    {
        if ($filterService->getFilters()) {
            if ($search = $filterService->getFilterValue('search')) {
                $query->andWhere('
                    task.title LIKE :search
                    OR task.description LIKE :search
                    OR company.name LIKE :search
                ');
                $query->setParameter('search', "%$search%");
            }
        }
    }

    public function addOrders(QueryBuilder &$query, FilterService $filterService): void
    {
        if ($orders = $filterService->getOrders()) {
            $field          = null;
            $orderDirection = null;
            foreach ($orders as $order) {
                $orderDirection = $order['order'];
                switch ($order['field']) {
                    case "title":
                        $field = "task.name";
                        break;
                    case "comments":
                        $field = "task.description";
                        break;
                    case "company":
                        $field = "company.id";
                        break;
                }
            }
            $query->addOrderBy($field, $orderDirection);
        }
    }

    public function createTask(
        User $user,
        ?Appointment $appointment,
        ?Client $client,
        Status $status,
        string $title,
        ?string $description,
        DateTime $estimatedStartDate
    ): Task
    {
        $task = new Task();

        $task->setTitle($title)
            ->setDescription($description)
            ->setEstimatedStartDate($estimatedStartDate)
            ->setUser($user)
            ->setAppointment($appointment)
            ->setClient($client)
            ->addStatus($status)
            ->setCurrentStatus($task->getLastStatus())
           ;


        $this->save($this->_em, $task);
        return $task;
    }

    public function updateTask(
        Task $task,
        string $title,
        ?string $description,
        DateTime $estimatedStartDate
    ): Task
    {
        $task->setTitle($title)
            ->setDescription($description)
            ->setEstimatedStartDate($estimatedStartDate)
        ;

        $this->save($this->_em, $task);
        return $task;
    }

    public function changeStatus(
        Task $task,
        Status $status
    ): Task
    {

        $task->addStatus($status);
        $task->setCurrentStatus($task->getLastStatus());

        if($status->getId() == Status::STATUS_TASK_COMPLETED){
            $task->setEndDate($task->getEstimatedStartDate());
        }

        $this->save($this->_em, $task);
        return $task;
    }

    public function addTime(
        Task $task,
        int $timestamp
    ): Task
    {

        $task->setTimestamp($task->getTimestamp() + $timestamp);

        $this->save($this->_em, $task);
        return $task;
    }


    public function deleteTask(Task $task)
    {
        $this->delete($this->_em, $task);
    }


}