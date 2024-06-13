<?php

namespace App\Controller\AppController;

use App\Service\CenterService\CenterService;
use App\Service\SecurityService\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/clientUser')]
class AppController extends AbstractController
{

    public function __construct(
        private readonly SecurityService $securityService,
        private readonly CenterService $centerService
    )
    {
    }

    #[Route(path: '/login', name: 'app_login_client')]
    public function clientLogin(): Response
    {
        return $this->securityService->clientLogin();
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LOGIN WITH A CLIENT
     * ES: ENDPOINT PARA LOGUEAR CON UN CLIENTE
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/app-client-index', name: 'app_client_index')]
    public function appClientIndex(): Response
    {
        return $this->centerService->appGetCenters();
    }
}