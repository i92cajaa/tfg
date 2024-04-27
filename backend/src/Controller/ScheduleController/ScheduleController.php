<?php

namespace App\Controller\ScheduleController;

use App\Annotation\Permission;
use App\Service\ScheduleService\ScheduleService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScheduleController extends AbstractController
{

    public function __construct(private readonly ScheduleService $scheduleService)
    {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LIST ALL LESSONS INSIDE A CALENDAR
     * ES: ENDPOINT PARA LISTAR TODAS LAS CLASES DENTRO DEL CALENDARIO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/', name: 'schedule_index', methods: ["GET"])]
    #[Permission(group: 'schedules', action: 'list')]
    public function list(): Response
    {
        return $this->scheduleService->index();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO GET ALL EVENTS FROM SCHEDULES
     * ES: ENDPOINT PARA OBTENER LOS EVENTOS DE TODOS LOS HORARIOS
     *
     * @return JsonResponse
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/get-schedules', name: 'schedule_get_json', methods: ["GET"])]
    #[Permission(group: 'schedules', action:"list")]
    public function getSchedulesJson(): JsonResponse
    {
        return $this->json($this->scheduleService->getEventsSchedulesFromRequest());
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO GET ALL TIMES AVAILABLE FOR A SELECTED DAY AND ROOM
     * ES: ENDPOINT PARA OBTENER LOS HORARIOS DISPONIBLES PARA UN DÍA Y HABITACIÓN SELECCIONADOS
     *
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/get-times', name: 'schedule_get_available_times', methods: ["POST"])]
    #[Permission(group: 'schedules', action:"list")]
    public function getScheduleTimes(): Response
    {
        return $this->scheduleService->getScheduleTimes();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO GET SCHEDULES BY LESSON
     * ES: ENDPOINT PARA OBTENER LOS HORARIOS DE UNA CLASE
     *
     * @return Response
     * @throws Exception
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/get-by-lesson', name: 'schedule_get_by_lesson', methods: ["POST"])]
    #[Permission(group: 'schedules', action:"list")]
    public function getSchedulesByLesson(): Response
    {
        return $this->scheduleService->getSchedulesByLesson();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW A SCHEDULE'S DATA
     * ES: ENDPOINT PARA MOSTRAR LA INFORMACIÓN DE UN HORARIO
     *
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/show/{schedule}', name: 'schedule_show', defaults: ["schedule" => null], methods: ["GET", "POST"])]
    #[Permission(group: 'schedules', action: 'show')]
    public function show(string $schedule): Response
    {
        return $this->scheduleService->show($schedule);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CREATE A NEW SCHEDULE
     * ES: ENDPOINT PARA CREAR UN HORARIO NUEVO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/new', name: 'schedule_create', methods: ["GET", "POST"])]
    #[Permission(group: 'schedules', action: 'create')]
    public function new(): Response
    {
        return $this->scheduleService->new();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO EDIT A SCHEDULE
     * ES: ENDPOINT PARA EDITAR UN HORARIO
     *
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/edit/{schedule}', name: 'schedule_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'schedules', action: 'edit')]
    public function edit(string $schedule): Response
    {
        return $this->scheduleService->edit($schedule);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CHANGE A SCHEDULE'S STATUS
     * ES: ENDPOINT PARA CAMBIAR EL ESTADO DE UN HORARIO
     *
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/change-status/{schedule}', name: 'change_status_schedule', methods: ["GET", "POST"])]
    #[Permission(group: 'schedules', action: 'edit')]
    public function changeStatus(string $schedule): Response
    {
        return $this->scheduleService->changeStatus($schedule);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO DELETE A SCHEDULE
     * ES: ENDPOINT PARA BORRAR UN HORARIO
     *
     * @param string $schedule
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/delete/{schedule}', name: 'schedule_delete', methods: ["POST"])]
    #[Permission(group: 'schedules', action: 'delete')]
    public function delete(string $schedule): Response
    {
        return $this->scheduleService->delete($schedule);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO TOGGLE MENU
     * ES: ENDPOINT PARA ALTERNAR EL MENÚ
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/toggle-menu', name: 'toggle_menu')]
    public function toggleMenuExpanded(): Response
    {
        return $this->scheduleService->toggleMenuExpanded();
    }
    // ----------------------------------------------------------------
}