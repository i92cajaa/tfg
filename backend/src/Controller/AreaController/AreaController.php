<?php

namespace App\Controller\AreaController;

use App\Annotation\Permission;
use App\Service\AreaService\AreaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/area')]
class AreaController extends AbstractController
{
    public function __construct(private readonly AreaService $areaService)
    {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LIST ALL AREAS
     * ES: ENDPOINT PARA LISTAR TODAS LAS ÁREAS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/', name: 'area_index', methods: ["POST"])]
    #[Permission(group: 'areas', action: 'list')]
    public function list(): Response
    {
        return $this->areaService->index();
    }
    // ----------------------------------------------------------------
}