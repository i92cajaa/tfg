<?php

namespace App\Controller\ScheduleController;

use App\Annotation\Permission;
use App\Service\ScheduleService\ScheduleService;
use Doctrine\ORM\NonUniqueResultException;
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
     * EN: ENDPOINT TO SHOW A SCHEDULE'S DATA
     * ES: ENDPOINT PARA MOSTRAR LA INFORMACIÓN DE UN HORARIO
     *
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedule/show/{schedule}', name: 'schedule_show', defaults: ["schedule" => null], methods: ["GET"])]
    #[Permission(group: 'schedules', action: 'show')]
    public function show(string $schedule): Response
    {
        return $this->scheduleService->show($schedule);
    }
    // ----------------------------------------------------------------
}