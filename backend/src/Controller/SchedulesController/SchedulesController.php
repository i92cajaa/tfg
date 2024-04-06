<?php

namespace App\Controller\SchedulesController;

use App\Entity\Schedules\Schedules;
use App\Entity\User\User;
use App\Repository\AppointmentRepository;
use App\Repository\FestiveRepository;
use App\Repository\SchedulesRepository;
use App\Repository\UserRepository;
use App\Service\ConfigService;
use App\Service\FilterService;
use App\Service\SchedulesService\SchedulesService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Annotation\Permission;


#[Route(path: '/schedules')]
class SchedulesController extends AbstractController
{

    public function __construct(
        private readonly SchedulesService $schedulesService
    )
    {
    }

    #[Route(path: '/show/{user}', name: 'schedules_show', methods: ["POST", "GET"])]
    #[Permission(group: 'users', action:"manage_schedules")]
    public function show(string $user): Response
    {
        return $this->schedulesService->show($user);
    }

    #[Route(path: '/new', name: 'schedules_new', methods: ["POST", "GET"])]
    #[Permission(group: 'users', action:"manage_schedules")]
    public function new(): Response
    {
        return $this->schedulesService->new();
    }

    #[Route(path: '/copy', name: 'schedules_copy', methods: ["POST"])]
    #[Permission(group: 'users', action:"manage_schedules")]
    public function copy(): Response
    {
        return $this->schedulesService->copySchedules();
    }

    #[Route(path: '/edit', name: 'schedules_edit', methods: ["POST"])]
    #[Permission(group: 'users', action:"manage_schedules")]
    public function edit(): Response
    {
        return $this->schedulesService->edit();
    }

    #[Route(path: '/toggle', name: 'schedules_toggle', methods: ["POST"])]
    #[Permission(group: 'users', action:"manage_schedules")]
    public function toggle(): RedirectResponse
    {
        return $this->schedulesService->toggle();
    }

    #[Route(path: '/delete', name: 'schedules_delete', methods: ["POST"])]
    #[Permission(group: 'users', action:"manage_schedules")]
    public function delete(): Response
    {
        return $this->schedulesService->delete();
    }

    #[Route(path: '/available-dates', name: 'schedules_get_available_dates', methods: ["POST"])]
    public function availableDates(): Response
    {
        return $this->schedulesService->availableDates();
    }

    #[Route(path: '/availables', name: 'schedules_get_availables', methods: ["POST"])]
    public function availables(): Response
    {

        return $this->schedulesService->availables();
    }

    #[Route(path: '/availables-by-appointment-and-date', name: 'schedules_get_availables_by_appointment_and_date', methods: ["POST"])]
    public function getSchedulesByAppointmentAndDate(): JsonResponse
    {
        return $this->schedulesService->getSchedulesByAppointmentAndDate();
    }

    #[Route(path: '/check', name: 'schedules_get_periodicity', methods: ["POST"])]
    public function checkPeriodicity(): Response
    {
        return $this->schedulesService->checkPeriodicity();
    }
}
