<?php

namespace App\Controller\AppController;

use App\Service\CenterService\CenterService;
use App\Service\LessonService\LessonService;
use App\Service\SecurityService\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/app')]
class AppController extends AbstractController
{

    public function __construct(
        private readonly SecurityService $securityService,
        private readonly CenterService $centerService,
        private readonly LessonService $lessonService,
    )
    {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LOGIN WITH A CLIENT
     * ES: ENDPOINT PARA LOGUEAR CON UN CLIENTE
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/login', name: 'app_login_client')]
    public function clientLogin(): Response
    {
        return $this->securityService->clientLogin();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO OBTAIN ALL CENTERS' INFO
     * ES: ENDPOINT PARA OBTENER TODA LA INFORMACIÓN DE LOS CENTROS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/all-centers', name: 'app_client_index')]
    public function getAllCenters(): Response
    {
        return $this->centerService->appGetCenters();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO OBTAIN ALL CENTERS' LESSONS
     * ES: ENDPOINT PARA OBTENER TODA LAS CLASES DE LOS CENTROS
     *
     * @param string $center
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/lessons-by-center/{center}', name: 'app_lessons_by_center')]
    public function getAllLessonsByCenterId(string $center): Response
    {
        return $this->lessonService->appGetLessonsByCenterId($center);
    }
    // ----------------------------------------------------------------
}