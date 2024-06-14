<?php

namespace App\Controller\CenterController;

use App\Annotation\Permission;
use App\Service\CenterService\CenterService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/center')]
class CenterController extends AbstractController
{
    public function __construct(private readonly CenterService $centerService)
    {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LIST ALL CENTERS
     * ES: ENDPOINT PARA LISTAR TODOS LOS CENTROS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/', name: 'center_index', methods: ["GET"])]
    #[Permission(group: 'centers', action:"list")]
    public function index(): Response
    {

        return $this->centerService->index();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW A CENTER'S DATA
     * ES: ENDPOINT PARA MOSTRAR LA INFORMACIÓN DE UN CENTRO
     *
     * @param string $center
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/show/{center}', name: 'center_show', methods: ["GET",'POST'])]
    #[Permission(group: 'centers', action:"show")]
    public function show(string $center): Response
    {
        return $this->centerService->show($center);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CREATE A NEW CENTER
     * ES: ENDPOINT PARA CREAR UN CENTRO NUEVO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/new', name: 'center_new', methods: ["GET",'POST'])]
    #[Permission(group: 'centers', action:"create")]
    public function new(): Response
    {
        return $this->centerService->new();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO EDIT A CENTER'S DATA
     * ES: ENDPOINT PARA EDITAR LOS DATOS DE UN CENTRO
     *
     * @param string $center
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/edit/{center}', name: 'center_edit', methods: ["GET",'POST'])]
    #[Permission(group: 'centers', action:"edit")]
    public function edit(string $center): Response
    {
        return $this->centerService->edit($center);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO DELETE A CENTER
     * ES: ENDPOINT PARA BORRAR UN CENTRO
     *
     * @param string $center
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/delete/{center}', name: 'center_delete', methods: ["GET",'POST'])]
    #[Permission(group: 'centers', action:"delete")]
    public function delete(string $center): Response
    {
        return $this->centerService->delete($center);
    }
    // ----------------------------------------------------------------
}