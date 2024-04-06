<?php

namespace App\Controller\DashboardController;

use App\Repository\AppointmentRepository;
use App\Repository\FestiveRepository;
use App\Repository\ClientRepository;
use App\Repository\SchedulesRepository;
use App\Repository\UserRepository;
use App\Service\ConfigService\ConfigService;
use App\Service\DashboardService\DashboardService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly DashboardService $dashboardService
    )
    {
    }

    #[Route(path: '/dashboard', name: 'dashboard')]
    public function index(): Response
    {
        return $this->dashboardService->index();
    }

    #[Route(path: '/availables', name: 'dashboard_update_widgets', methods: ["POST"])]
    public function availables(): Response
    {
        return $this->dashboardService->availables();
    }

    #[Route(path: '/toggle', name: 'toggle_mode')]
    public function toggleMode(): Response
    {
        return $this->dashboardService->toggleMode();
    }

    #[Route(path: '/toggle-menu', name: 'toggle_menu')]
    public function toggleMenuExpanded(): Response
    {
        return $this->dashboardService->toggleMenuExpanded();
    }
}
