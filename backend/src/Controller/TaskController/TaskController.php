<?php

namespace App\Controller\TaskController;

use App\Entity\Task\Task;
use App\Repository\TaskRepository;
use App\Service\TaskService\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;


#[Route(path: '/task')]
class TaskController extends AbstractController
{

    public function __construct(
        private readonly TaskService $taskService
    )
    {
    }

    #[Route(path: ['/planner'], name: 'task_planner', methods: ["GET"])]
    public function planner(): Response
    {
        return $this->taskService->renderPlanner();
    }

    #[Route(path: '/list', name: 'task_list', methods: ["GET"])]
    public function list(): Response
    {
        return $this->taskService->renderList();
    }

    #[Route(path: '/get-tasks', name: 'task_get_json', methods: ["GET"])]
    public function getTasksJson(): JsonResponse
    {

        return $this->taskService->getEventsTasksFromRequest();
    }

    #[Route(path: '/get-task', name: 'task_get_by_id', methods: ["POST","GET"])]
    public function getTaskJson(Request $request): JsonResponse
    {

        return $this->taskService->getTaskJson($request);
    }

    #[Route(path: '/get-edit-template', name: 'task_get_edit_template', methods: ["POST"])]
    public function getEditTaskTemplate(): Response
    {
        return $this->taskService->getEditTaskTemplate();
    }

    #[Route(path: '/get-create-template', name: 'task_get_create_template', methods: ["POST"])]
    public function getCreateTaskTemplate(): Response
    {
        return $this->taskService->getCreateTaskTemplate();
    }

    #[Route(path: '/create', name: 'task_process_new', methods: ["POST"])]
    public function createTaskProcess(): Response
    {
        return $this->taskService->createTaskFromRequest();
    }

    #[Route(path: '/edit', name: 'task_process_edit', methods: ["POST"])]
    public function editTaskProcess(): Response
    {
        return $this->taskService->editTaskFromRequest();
    }

    #[Route(path: '/get-schema', name: 'task_schema_get', methods: ["POST"])]
    public function getSchema(): Response
    {
        return $this->taskService->getSchema();
    }

    #[Route(path: '/change-status', name: 'task_change_status', methods: ["POST"])]
    public function changeStatusTaskProcess(): Response
    {
        return $this->taskService->changeStatusTaskFromRequest();
    }

    #[Route(path: '/add-time', name: 'task_add_time', methods: ["POST"])]
    public function addTimeTaskProcess(): Response
    {
        return $this->taskService->addTimeTaskFromRequest();
    }

    #[Route(path: '/delete/{task}', name: 'task_delete', defaults:["task" => null],  methods: ["POST"])]
    public function delete(string $task): Response
    {
        return $this->taskService->deleteTask($task);
    }

}
