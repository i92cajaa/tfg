<?php

namespace App\Service\ScheduleService;

use App\Entity\Schedule\Schedule;
use App\Entity\Status\Status;
use App\Form\ScheduleType;
use App\Repository\CenterRepository;
use App\Repository\LessonRepository;
use App\Repository\RoomRepository;
use App\Repository\ScheduleRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\ConfigService\ConfigService;
use App\Service\FilterService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use function Symfony\Component\String\s;

class ScheduleService extends AbstractService
{

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository,
        private readonly UserRepository $userRepository,
        private readonly StatusRepository $statusRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly RoomRepository $roomRepository,
        private readonly ConfigService $configService,
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
        if ($this->getUser()->isTeacher()) {
            $this->filterService->addFilter('users', [$this->getUser()->getId()]);
            $this->filterService->addFilter('status', true);
        } elseif ($this->getUser()->isAdmin() && !$this->getUser()->isSuperAdmin()) {
            $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
        }

        $from = (UTCDateTime::create())->modify('first day of this month')->format('d/m/Y');
        $to = (UTCDateTime::create())->modify('last day of this month')->format('d/m/Y');

        $this->filterService->addFilter('min_date', $from);
        $this->filterService->addFilter('max_date', $to);

        $schedules = $this->scheduleRepository->findSchedules($this->filterService, true);

        return $this->render('schedule/index.html.twig', [
            'schedules' => $schedules,
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
     * EN: SERVICE TO GET ALL TIMES AVAILABLE FOR A SELECTED DAY AND ROOM
     * ES: SERVICIO PARA OBTENER LOS HORARIOS DISPONIBLES PARA UN DÍA Y HABITACIÓN SELECCIONADOS
     *
     * @return JsonResponse
     * @throws NonUniqueResultException
     * @throws Exception
     */
    // ----------------------------------------------------------------
    public function getScheduleTimes(): JsonResponse
    {
        $this->filterService->addFilter('teacher', $this->getRequestPostParam('teacher'));
        $this->filterService->addFilter('min_date', $this->getRequestPostParam('date'));
        $this->filterService->addFilter('max_date', $this->getRequestPostParam('date'));

        $schedules = $this->scheduleRepository->findSchedules($this->filterService, true)['schedules'];

        $this->filterService = $this->filterService->getNewFilter();

        $this->filterService->addFilter('room', $this->getRequestPostParam('room'));
        $this->filterService->addFilter('min_date', $this->getRequestPostParam('date'));
        $this->filterService->addFilter('max_date', $this->getRequestPostParam('date'));

        $schedules += $this->scheduleRepository->findSchedules($this->filterService, true)['schedules'];

        $lesson = $this->lessonRepository->findById($this->getRequestPostParam('lesson'), false);
        $duration = $lesson->getDuration();

        $availableRanges = $this->formatDayIntoRanges($lesson->getCenter()->getOpeningTime(), $lesson->getCenter()->getClosingTime(), $duration, $schedules);

        return new JsonResponse(['ranges' => $availableRanges, 'status' => true]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO GET SCHEDULES BY LESSON
     * ES: SERVICIO PARA OBTENER LOS HORARIOS DE UNA CLASE
     *
     * @return JsonResponse
     * @throws Exception
     */
    // ----------------------------------------------------------------
    public function getSchedulesByLesson(): JsonResponse
    {
        $this->filterService->addFilter('lesson', $this->getRequestPostParam('lesson'));
        $this->filterService->addFilter('min_date', UTCDateTime::create()->format('d/m/Y'));
        $this->filterService->addFilter('status', Status::STATUS_AVAILABLE);

        $schedules = $this->scheduleRepository->findSchedules($this->filterService, true, true)['schedules'];

        return new JsonResponse(['schedules' => $schedules, 'status' => true]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO FORMAT SCHEDULES INTO EVENTS
     * ES: SERVICIO PARA FORMATEAR HORARIOS A EVENTOS
     *
     * @param DateTime $openingTime
     * @param DateTime $closingTime
     * @param float $duration
     * @param array|Collection $schedules
     * @return array
     * @throws Exception
     */
    // ----------------------------------------------------------------
    public function formatDayIntoRanges(DateTime $openingTime, DateTime $closingTime,
                                        float $duration, array|Collection $schedules): array
    {
        $availableRanges = [];
        $currentOpeningTime = clone $openingTime;

        while ($currentOpeningTime < $closingTime) {
            $currentClosingTime = clone $currentOpeningTime;
            $hours = floor($duration);
            $minutes = ($duration - $hours) * 60;
            $currentClosingTime->add(new \DateInterval('PT' . $hours . 'H' . $minutes . 'M'));

            $inBetween = false;
            foreach ($schedules as $schedule) {
                $scheduleDateFrom = clone $schedule->getDateFrom();
                $scheduleDateFrom->setDate(
                    $currentOpeningTime->format('Y'),
                    $currentOpeningTime->format('m'),
                    $currentOpeningTime->format('d')
                );
                $scheduleDateTo = clone $schedule->getDateTo();
                $scheduleDateTo->setDate(
                    $currentOpeningTime->format('Y'),
                    $currentOpeningTime->format('m'),
                    $currentOpeningTime->format('d')
                );

                if (($scheduleDateFrom >= $currentOpeningTime && $scheduleDateFrom < $currentClosingTime) ||
                    ($scheduleDateTo > $currentOpeningTime && $scheduleDateTo <= $currentClosingTime)) {
                    $inBetween = true;
                    break;
                }
            }

            if (!$inBetween) {
                $availableRanges[] = [
                    'start' => $currentOpeningTime->format('H:i'),
                    'end' => $currentClosingTime->format('H:i')
                ];
            }

            $currentOpeningTime = $currentClosingTime;
        }

        return $availableRanges;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO FORMAT DATE INTO AVAILABLE RANGES
     * ES: SERVICIO PARA FORMATEAR UN DÍA EN RANGOS DISPONIBLES
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
            $this->getRequestPostParam('schedule')['range'] !== null &&
            $this->getRequestPostParam('schedule')['day'] !== null)
        {
            $day = $this->getRequestPostParam('schedule')['day'];
            $range = explode(' - ', $this->getRequestPostParam('schedule')['range']);

            $schedule->setDateFrom(UTCDateTime::create('d/m/Y H:i', $day . ' ' . $range[0]));
            $schedule->setDateTo(UTCDateTime::create('d/m/Y H:i', $day . ' ' . $range[1]));

            $schedule->setStatus($this->statusRepository->find(Status::STATUS_AVAILABLE));

            $this->scheduleRepository->save($schedule, true);

            return $this->redirectToRoute('schedule_index');
        }

        if ($this->getUser()->isAdmin() && !$this->getUser()->isSuperAdmin()) {
            $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
        }

        $this->filterService->addFilter('roles', 3);
        $this->filterService->addFilter('status', true);
        $users = $this->userRepository->findUsers($this->filterService, true)['users'];

        return $this->render('schedule/new.html.twig', [
            'schedule' => $schedule,
            'users' => $users,
            'edit' => false,
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
            $this->getRequestPostParam('schedule')['day'] !== null)
        {
            $day = $this->getRequestPostParam('schedule')['day'];

            if (isset($this->getRequestPostParam('schedule')['range'])) {
                $range = explode(' - ', $this->getRequestPostParam('schedule')['range']);

                $schedule->setDateFrom(UTCDateTime::create('d/m/Y H:i', $day . ' ' . $range[0]));
                $schedule->setDateTo(UTCDateTime::create('d/m/Y H:i', $day . ' ' . $range[1]));
            }

            $this->scheduleRepository->save($schedule, true);

            return $this->redirectToRoute('schedule_index');
        }

        if ($this->getUser()->isAdmin() && !$this->getUser()->isSuperAdmin()) {
            $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
        }

        $this->filterService->addFilter('roles', 3);
        $this->filterService->addFilter('status', true);
        $users = $this->userRepository->findUsers($this->filterService, true)['users'];

        $this->filterService->addFilter('teacher', $schedule->getTeacher()->getId());
        $lessons = $this->lessonRepository->findLessons($this->filterService, true)['lessons'];

        $this->filterService->addFilter('center', $schedule->getTeacher()->getCenter()->getId());
        $rooms = $this->roomRepository->findRooms($this->filterService, true)['rooms'];

        return $this->render('schedule/edit.html.twig', [
            'schedule' => $schedule,
            'users' => $users,
            'edit' => true,
            'lessons' => $lessons,
            'rooms' => $rooms,
            'form' => $form->createView()
        ]);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CHANGE A SCHEDULE'S STATUS
     * ES: SERVICIO PARA CAMBIAR EL ESTADO DE UN HORARIO
     *
     * @param string $scheduleId
     * @param int|null $status
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function changeStatus(string $scheduleId, ?int $status = null): Response
    {
        $schedule = $this->scheduleRepository->findById($scheduleId, false);

        $status = $this->statusRepository->find($status ?: $this->getRequestPostParam('status'));

        if ($status->getId() == Status::STATUS_CANCELED) {
            //$this->email($schedule);
        }

        $schedule->setStatus($status);

        $this->scheduleRepository->save($schedule, true);

        return $this->redirectToRoute('schedule_show', ['schedule' => $schedule->getId()]);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO DELETE A SCHEDULE
     * ES: SERVICIO PARA BORRAR UN HORARIO
     *
     * @param string $scheduleId
     * @return Response
     */
    // ----------------------------------------------------------------
    public function delete(string $scheduleId): Response
    {
        $schedule = $this->getEntity($scheduleId);
        $this->scheduleRepository->remove($schedule,true);

        return $this->redirectToRoute('schedule_index');
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO TOGGLE MENU
     * ES: SERVICIO PARA ALTERNAR EL MENÚ
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function toggleMenuExpanded(): Response
    {
        $this->configService->toggleMenuExpanded();
        $user = $this->getUser();
        $newUser = $this->userRepository->find($user->getId());

        if($newUser->isMenuExpanded()){
            $newUser->setMenuExpanded(false);
        }else{
            $newUser->setMenuExpanded(true);
        }

        $this->userRepository->persist($newUser);

        return new Response("Modo guardado satisfactoriamente", 200);
    }
    // ----------------------------------------------------------------
}