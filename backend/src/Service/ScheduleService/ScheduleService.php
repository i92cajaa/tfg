<?php

namespace App\Service\ScheduleService;

use App\Entity\Schedule\Schedule;
use App\Entity\Status\Status;
use App\Form\ScheduleType;
use App\Repository\ScheduleRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ScheduleService extends AbstractService
{

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository,
        private readonly UserRepository $userRepository,
        private readonly StatusRepository $statusRepository,
        EntityManagerInterface $em,
        RouterInterface $router,
        Environment $twig,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface $tokenManager,
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        TranslatorInterface $translator
    )
    {
        parent::__construct(
            requestStack: $requestStack,
            router: $router,
            twig: $twig,
            tokenStorage: $tokenStorage,
            tokenManager: $tokenManager,
            formFactory: $formFactory,
            serializer: $serializer,
            translator: $translator,
            entityRepository: $this->scheduleRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL SCHEDULES INSIDE A CALENDAR
     * ES: SERVICIO PARA LISTAR TODOS LOS HORARIOS DENTRO DE UN CALENDARIO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {
        $users = [];

        if ($this->getUser()->isTeacher()) {
            $this->filterService->addFilter('users', [$this->getUser()->getId()]);
            $this->filterService->addFilter('status', true);
        }else{
            $users = $this->userRepository->findAll();
        }

        $from = (UTCDateTime::create())->modify('first day of this month')->format('d-m-Y');
        $to = (UTCDateTime::create())->modify('last day of this month')->format('d-m-Y');

        $this->filterService->addFilter('min_date', $from);
        $this->filterService->addFilter('max_date', $to);

        $schedules = $this->scheduleRepository->findSchedules($this->filterService, true);

        return $this->render('schedule/index.html.twig', [
            'schedules' => $schedules,
            'users' => $users,
            'filterService' => $this->filterService
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO GET ALL EVENTS FROM SCHEDULES
     * ES: SERVICIO PARA OBTENER LOS EVENTOS DE TODOS LOS HORARIOS
     *
     * @return array
     */
    // ----------------------------------------------------------------
    public function getEventsSchedulesFromRequest(): array
    {
        $this->filterService->setLimit(5000);
        $isSuperAdmin = false;

        if ($this->getUser()->isSuperAdmin()) {
            $isSuperAdmin = true;
        } else {
            $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
            $this->filterService->addFilter('user', $this->getUser()->getId());
        }

        $this->filterService->addFilter('dateRange', $this->filterService->getFilterValue('date_from') . ' a ' . $this->filterService->getFilterValue('date_to'));
        $this->filterService->addFilter('date_from', null);
        $this->filterService->addFilter('date_to', null);

        $schedules = $this->scheduleRepository->findSchedules($this->filterService, true)['schedules'];

        return [
            'success' => true,
            'message' => 'OK',
            'data'    => $this->formatSchedulesToEvents($schedules, $isSuperAdmin)
        ];
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO FORMAT SCHEDULES INTO EVENTS
     * ES: SERVICIO PARA FORMATEAR HORARIOS A EVENTOS
     *
     * @param array $schedules
     * @param bool $isSuperAdmin
     * @return array
     */
    // ----------------------------------------------------------------
    public function formatSchedulesToEvents(array $schedules, bool $isSuperAdmin = false): array
    {
        $scheduleEvents = [];
        foreach ($schedules as $schedule) {
            $scheduleEvents[] = $schedule->toEvent($isSuperAdmin);
        }
        return $scheduleEvents;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW A SCHEDULE'S DATA
     * ES: SERVICIO PARA MOSTRAR LOS DATOS DE UN HORARIO
     *
     * @param string $scheduleId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function show(string $scheduleId): Response
    {
        $schedule = $this->scheduleRepository->findById($scheduleId, false);

        return $this->render('schedule/show.html.twig', [
            'schedule' => $schedule,
            'statuses' => $this->statusRepository->findAll()
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CREATE A NEW SCHEDULE
     * ES: SERVICIO PARA CREAR UN HORARIO NUEVO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function new(): Response
    {
        $schedule = new Schedule();
        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() &&
            $this->getRequestPostParam('schedule')['date_from'] != null &&
            $this->getRequestPostParam('schedule')['date_to'] != null)
        {
            $schedule->setDateFrom(UTCDateTime::create('d/m/Y H:i', $form->get('date_from')->getViewData()));
            $schedule->setDateTo(UTCDateTime::create('d/m/Y H:i', $form->get('date_to')->getViewData()));

            $schedule->setStatus($this->statusRepository->find(Status::STATUS_AVAILABLE));

            $this->scheduleRepository->save($schedule, true);

            return $this->redirectToRoute('schedule_index');
        }

        $this->filterService->addFilter('roles', 3);

        $users = $this->userRepository->findUsers($this->filterService, true)['users'];

        return $this->render('schedule/new.html.twig', [
            'schedule' => $schedule,
            'users' => $users,
            'form' => $form->createView()
        ]);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO EDIT A SCHEDULE
     * ES: SERVICIO PARA EDITAR UN HORARIO
     *
     * @param string $scheduleId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function edit(string $scheduleId): Response
    {
        $schedule = $this->scheduleRepository->findById($scheduleId, false);
        $form = $this->createForm(ScheduleType::class, $schedule);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() &&
            $this->getRequestPostParam('schedule')['date_from'] != null &&
            $this->getRequestPostParam('schedule')['date_to'] != null)
        {
            $schedule->setDateFrom(UTCDateTime::create('d/m/Y H:i', $form->get('date_from')->getViewData()));
            $schedule->setDateTo(UTCDateTime::create('d/m/Y H:i', $form->get('date_to')->getViewData()));

            $schedule->setStatus($this->statusRepository->find(Status::STATUS_AVAILABLE));

            $this->scheduleRepository->save($schedule, true);

            return $this->redirectToRoute('schedule_index');
        }

        $this->filterService->addFilter('roles', 3);

        $users = $this->userRepository->findUsers($this->filterService, true)['users'];

        return $this->render('schedule/edit.html.twig', [
            'schedule' => $schedule,
            'users' => $users,
            'form' => $form->createView()
        ]);

    }
    // ----------------------------------------------------------------
}