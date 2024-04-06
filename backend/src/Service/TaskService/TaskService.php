<?php

namespace App\Service\TaskService;


use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\Status\Status;
use App\Entity\Task\Task;
use App\Entity\User\User;
use App\Repository\AppointmentRepository;
use App\Repository\ClientRepository;
use App\Repository\StatusRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\AppointmentService\AppointmentService;
use App\Service\ClientService\ClientService;
use App\Service\FilterService;
use App\Service\NotificationService\NotificationService;use App\Service\UserService\UserPermissionService;
use App\Service\UserService\UserService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Utils\Util;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TaskService extends AbstractService
{

    /**
     * @var TaskRepository
     */
    private TaskRepository $taskRepository;

    private StatusRepository $statusRepository;

    private ClientRepository $clientRepository;

    private UserRepository $userRepository;

    private AppointmentRepository $appointmentRepository;


    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly UserPermissionService $userPermissionService,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserService $userService,
        private readonly AppointmentService $appointmentService,
        private readonly ClientService $clientService,

        EntityManagerInterface $em,

        RouterInterface       $router,
        Environment           $twig,
        RequestStack          $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface      $tokenManager,
        FormFactoryInterface           $formFactory,
        SerializerInterface            $serializer,
        TranslatorInterface $translator

    )
    {
        $this->taskRepository = $em->getRepository(Task::class);
        $this->statusRepository = $em->getRepository(Status::class);
        $this->clientRepository = $em->getRepository(Client::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->appointmentRepository = $em->getRepository(Appointment::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->taskRepository
        );
    }


    public function getTaskById(int $taskId): ?Task
    {

        return $this->taskRepository->findTaskById($taskId);
    }


    public function all(): ?array
    {
        return $this->taskRepository->findAll();
    }


    public function getTasksByIds(array $taskIds): ?array
    {
        return $this->taskRepository->getTasksByIds($taskIds);
    }

    public function renderPlanner(): Response
    {

        if(!$this->userPermissionService->can('assign_tasks', 'users')){
            $this->filterService->addFilter('user', $this->getUser()->getId());
        }
        $tasks = $this->taskRepository->findTasks($this->filterService);
        return $this->render('task/planner.html.twig', [
            'users' => $this->userService->getAll(),
            'totalResults' => $tasks['totalRegisters'],
            'lastPage' => $tasks['lastPage'],
            'currentPage' => $this->filterService->page,
            'tasks' => $tasks['data'],
            'filterService' => $this->filterService
        ]);
    }

    public function renderList()
    {
        $appointments = $this->appointmentRepository->findByUser($this->getUser());

        if($this->getUser()->isAdmin()){
            $users = $this->userRepository->findAll();
        }else{
            $this->filterService->addFilter('user', $this->getUser()->getId());
            $users = [$this->getUser()];
        };

        $tasks = $this->taskRepository->findTasks($this->filterService);
        //dd($this->filterService);
        //$services = $this->serviceRepository->findBy(['active'=>true]);

        return $this->render('task/list.html.twig', [
            'totalResults' => $tasks['totalRegisters'],
            'lastPage' => $tasks['lastPage'],
            'currentPage' => $this->filterService->page,
            'filterService' => $this->filterService,
            'tasks' => $tasks,
            'users' => $users,
            'appointments' => $appointments,
            'clients' => $this->clientRepository->findBy(['status'=>'1']),
            /*'services' => $services,
            'clientId' => $clientId,
            'divisions' => $this->divisionRepository->findAll(),
            'allServices' => $this->serviceRepository->findAll(),*/
            'status' => $this->statusRepository->findBy(['entityType' => Task::ENTITY])
        ]);
    }

    public function getEventsTasksFromRequest(): JsonResponse
    {
        $this->filterService->setLimit(5000);

        $this->filterService->addFilter('dateRange', $this->filterService->getFilterValue('date_from') . ' a ' . $this->filterService->getFilterValue('date_to'));
        $this->filterService->addFilter('date_from', null);
        $this->filterService->addFilter('date_to', null);

        if (!$this->getUser()->isAdmin()) {
            $this->filterService->addFilter('user', $this->getUser()->getId());
        }

        $tasks = $this->taskRepository->findTasks($this->filterService)['data'];

        return $this->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $this->formatTasksToEvents($tasks)
        ]);
    }

    public function getTaskJson(Request $request): JsonResponse
    {
        $task = null;
        if ($this->isCsrfTokenValid('get-task', $this->getRequestPostParam('_token'))) {
            $task = $this->taskRepository->find($this->getRequestPostParam('id'));
        }elseif ($request->request->get('_token')) {
            $task = $this->taskRepository->find($request->request->get('id'));
        }

        $arrayTask = $task->toArray($this->getTranslator());


        return $this->json([
            'success' => true,
            'message' => 'OK',
            'data'    => $arrayTask
        ]);
    }

    /**
     * @param Task[] $tasks
     * @return array
     */
    public function formatTasksToEvents(array $tasks): array
    {
        $taskEvents = [];
        foreach ($tasks as $task) {
            $taskEvents[] = $task->toEvent($this->getTranslator());
        }

        return $taskEvents;
    }

    public function getEditTaskTemplate(): JsonResponse
    {
        if ($this->isCsrfTokenValid('get-edit-task-template', $this->getRequestPostParam('_token'))) {

            $task = $this->taskRepository->find($this->getRequestPostParam('id'));

            if($task){
                $template = $this->renderView('task/edit_template.html.twig', [
                    'task' => $task,
                    'users' => $this->userService->getAll(),
                    'status' => $this->statusRepository->findBy(['entityType' => Task::ENTITY])
                ]);

                return new JsonResponse(['success' => true, 'data' => $template, 'message' => $this->translate('Task Form')]);
            }

            return new JsonResponse(['success' => false, 'message' => $this->translate('Task Not Found')]);

        }
        return new JsonResponse(['success' => false, 'message' => $this->translate('Invalid Token')]);
    }

    public function getCreateTaskTemplate(): JsonResponse
    {
        if ($this->isCsrfTokenValid('get-create-task-template', $this->getRequestPostParam('_token'))) {

            $template = $this->renderView('task/create_template.html.twig', [
                'users' => $this->userService->getAll(),
                'appointment' => @$this->getRequestPostParam('appointment'),
                'client' => @$this->getRequestPostParam('client'),
                'event' => @$this->getRequestPostParam('event'),
                'status' => @$this->getRequestPostParam('status')
            ]);

            return new JsonResponse(['success' => true, 'data' => $template, 'message' => $this->translate('Task Form')]);

        }
        return new JsonResponse(['success' => false, 'message' => $this->translate('Invalid Token')]);
    }



    public function editTaskFromRequest(): Response
    {
        if ($this->isCsrfTokenValid('edit-task', $this->getRequestPostParam('_token'))) {
            $task = $this->taskRepository->findTaskById($this->getRequestParam('id'));
            if (!$task) {
                $this->addFlash('error', $this->translate('Task Not Found'));
            }else{

                if (!$task) {
                    $this->addFlash('error',$this->translate('Task Not Found'));
                }else{

                    $status = $this->statusRepository->find($this->getRequestPostParam('status'));
                    if($status){
                        $taskUpdated = $this->taskRepository->changeStatus(
                            $task,
                            $status
                        );

                    }
                }
                $taskUpdated = $this->taskRepository->updateTask(
                    $task,
                    $this->getRequestParam('title'),
                    $this->getRequestParam('description'),
                    UTCDateTime::create('d/m/Y H:i', $this->getRequestPostParam('estimated_start_date'))
                );

                $this->addFlash('success',$this->translate('Task Updated Successfully'));

            }

        }else{
            $this->addFlash('error',$this->translate('Invalid Token'));
        }

        return $this->redirectBack();
    }

    public function getSchema(): JsonResponse
    {
        if ($this->isCsrfTokenValid('get-task-schema-template', $this->getRequestPostParam('_token'))) {

            $entity = null;
            $entityType = null;

            if(@$this->getRequestPostParam('client')){
                $entity = $this->clientService->find(@$this->getRequestPostParam('client'));
                $entityType = 'client';
            }

            if(@$this->getRequestPostParam('appointment')){
                $entity = $this->appointmentService->find(@$this->getRequestPostParam('appointment'));
                $entityType = 'appointment';
            }

            if($entity){
                $template = $this->renderView('task/schema.html.twig', [
                    'entity' => $entity,
                    'entityType' => $entityType
                ]);

                return new JsonResponse(['success' => true, 'data' => $template, 'message' => $this->translate('Task Form')]);

            }else{
                return new JsonResponse(['success' => false, 'message' => $this->translate('No valid entity provided')]);
            }

        }
        return new JsonResponse(['success' => false, 'message' => $this->translate('Invalid Token')]);
    }

    public function changeStatusTaskFromRequest(): Response
    {
        if ($this->isCsrfTokenValid('change-status-task', $this->getRequestPostParam('_token'))) {
            $task = $this->taskRepository->findTaskById($this->getRequestParam('id'));
            if (!$task) {
                $this->addFlash('error',$this->translate('Task Not Found'));
            }else{

                $status = $this->statusRepository->find($this->getRequestPostParam('status'));
                if($status){
                    $taskUpdated = $this->taskRepository->changeStatus(
                        $task,
                        $status
                    );

                }

                return new JsonResponse(['success' => true, 'message' => $this->translate('The Task Status Has Been Updated Successfully')]);

            }

        }else{
            return new JsonResponse(['success' => false, 'message' => $this->translate('Invalid Token')]);
        }

        return new JsonResponse(['success' => false, 'message' => $this->translate('Error when changing Task status')]);
    }

    public function addTimeTaskFromRequest(): Response
    {
        if ($this->isCsrfTokenValid('add-time-task', $this->getRequestPostParam('_token'))) {
            $task = $this->taskRepository->findTaskById($this->getRequestParam('id'));
            if (!$task) {
                $this->addFlash('error',$this->translate('Task Not Found'));
            }else{
                $datetime = @$this->getRequestPostParam('time');

                if($datetime){
                    $time = explode(':', $datetime);
                    $newDatetime = UTCDateTime::create();
                    $newDatetime->setTimestamp(0)->modify('+ ' . $time[0] . ' hours')->modify('+ ' . $time[1] . ' minutes');

                    $taskUpdated = $this->taskRepository->addTime(
                        $task,
                        $newDatetime->getTimestamp()
                    );

                }


                return new JsonResponse(['success' => true, 'message' => $this->translate('Time has been recorded to the Task correctly')]);

            }

        }else{
            return new JsonResponse(['success' => false, 'message' => $this->translate('Invalid Token')]);
        }

        return new JsonResponse(['success' => false, 'message' => $this->translate('Error when recording time in the Task')]);
    }



    public function createTaskFromRequest(): Response
    {
        if ($this->isCsrfTokenValid('create-task', $this->getRequestPostParam('_token'))) {

            foreach ($this->getRequestPostParam('users') as $userId) {
                $user = $this->userService->getUserById($userId);
                $appointment = @$this->getRequestPostParam('appointment') ? $this->appointmentService->findAppointmentById($this->getRequestPostParam('appointment')) : null;
                $client = @$this->getRequestPostParam('client') ? $this->clientService->find($this->getRequestPostParam('client')) : null;
                $status = @$this->getRequestPostParam('status') ? $this->statusRepository->find($this->getRequestPostParam('status')) : $this->statusRepository->find(Status::STATUS_TASK_PENDING);

                $task = $this->taskRepository->createTask(
                    $user,
                    $appointment,
                    $client,
                    $status,
                    $this->getRequestParam('title'),
                    $this->getRequestParam('description'),
                    UTCDateTime::create('d/m/Y H:i', $this->getRequestPostParam('estimated_start_date')),
                );

                if ($this->getUser() != $user) {
                    $this->notificationService->createNotification(
                        $this->translate('You have been assigned a new Task, to check your tasks click on the following link'),
                        $this->urlGenerator->generate('task_planner'),
                        $user
                    );
                }
            }


        }else{
            $this->addFlash('error', $this->translate('Invalid Token'));
        }

        return $this->redirectBack();

    }


    /**
     * @param Request $request
     * @param Task $task
     * @return Response
     */
    public function deleteTask(string $task): Response
    {
        if ($this->isCsrfTokenValid('delete-task', $this->getRequestPostParam('_token'))) {
            $task = $this->getEntity($task);

            if (!$task) {
                $this->addFlash('error', $this->translate('Task Not Found'));
            } else {

                $this->taskRepository->deleteTask($task);
                $this->addFlash('success', $this->translate('Task deleted successfully'));
            }
        }

        return $this->redirectBack();

    }


}
