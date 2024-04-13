<?php

namespace App\Controller\ScheduleController;

use App\Annotation\Permission;
use App\Service\ScheduleService\ScheduleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}