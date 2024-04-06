<?php

namespace App\Controller\ClientUserController;

use App\Service\AppointmentService\AppointmentService;
use App\Service\ClientUserService\ClientUserService;
use App\Service\SecurityService\SecurityService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/clientUser')]
class ClientUserController extends AbstractController
{


    public function __construct(
        private readonly ClientUserService $clientUserService,
        private readonly SecurityService $securityService,
        private readonly AppointmentService $appointmentService
    )
    {
    }

    #[Route(path: '/login', name: 'app_login_client')]
    public function login(): Response
    {
        return $this->securityService->login(true);
    }

    #[Route(path: '/index', name: 'app_client_index')]
    public function index(): Response
    {
        return $this->clientUserService->index();
    }

    #[Route(path: '/send_email', name: 'send_client_email')]
    public function emailNotification(): Response
    {
        return $this->clientUserService->sendEmail();
    }

    #[Route(path: '/get_appointment', name: 'app_client_appointment_json', methods: ["GET"])]
    public function getAppointment():JsonResponse
    {
        return $this->json($this->appointmentService->getEventsClientAppointmentsFromRequest());
    }

    #[Route(path: '/logout', name: 'app_logout_client')]
    public function logout():void
    {
        $this->securityService->logout();
    }

}