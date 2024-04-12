<?php

namespace App\Controller\AreaController;

use App\Annotation\Permission;
use App\Service\AreaService\AreaService;
use Doctrine\ORM\NonUniqueResultException;
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
    #[Route(path: '/', name: 'area_index', methods: ["GET"])]
    #[Permission(group: 'areas', action: 'list')]
    public function index(): Response
    {
        return $this->areaService->index();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW AN AREA'S DATA
     * ES: ENDPOINT PARA MOSTRAR LA INFORMACIÓN DE UN ÁREA
     *
     * @param string $area
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/show/{area}', name: 'area_show', methods: ["GET"])]
    #[Permission(group: 'areas', action: 'show')]
    public function show(string $area): Response
    {
        return $this->areaService->show($area);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CREATE A NEW AREA
     * ES: ENDPOINT PARA CREAR UN ÁREA NUEVA
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/new', name: 'area_create', methods: ["GET", "POST"])]
    #[Permission(group: 'areas', action: 'create')]
    public function new(): Response
    {
        return $this->areaService->new();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO EDIT AN AREA'S DATA
     * ES: ENDPOINT PARA EDITAR LOS DATOS DE UN ÁREA
     *
     * @param string $area
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/edit/{area}', name: 'area_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'areas', action: 'edit')]
    public function edit(string $area): Response
    {
        return $this->areaService->edit($area);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO DELETE AN AREA
     * ES: ENDPOINT PARA BORRAR UN ÁREA
     *
     * @param string $area
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/delete/{area}', name: 'area_delete', methods: ["GET", "POST"])]
    #[Permission(group: 'areas', action: 'delete')]
    public function delete(string $area): Response
    {
        return $this->areaService->delete($area);
    }
    // ----------------------------------------------------------------
}