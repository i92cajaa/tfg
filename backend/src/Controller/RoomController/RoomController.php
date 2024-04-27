<?php

namespace App\Controller\RoomController;

use App\Annotation\Permission;
use App\Service\RoomService\RoomService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/room')]
class RoomController extends AbstractController
{

    public function __construct(private readonly RoomService $roomService)
    {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LIST ALL ROOMS
     * ES: ENDPOINT PARA LISTAR TODAS LAS HABITACIONES
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/', name: 'room_index', methods: ["GET"])]
    #[Permission(group: 'rooms', action: 'list')]
    public function index(): Response
    {
        return $this->roomService->index();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO GET ALL ROOMS BY USER ID
     * ES: ENDPOINT PARA OBTENER TODAS LAS HABITACIONES POR ID DE USUARIO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/get-by-user', name: 'room_get_by_user', methods: ["POST"])]
    #[Permission(group: 'rooms', action: 'list')]
    public function getByUserId(): Response
    {
        return $this->roomService->getByUserId();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW A ROOM'S DATA
     * ES: ENDPOINT PARA MOSTRAR LA INFORMACIÓN DE UNA HABITACIÓN
     *
     * @param string $room
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/show/{room}', name: 'room_show', methods: ["GET"])]
    #[Permission(group: 'rooms', action: 'show')]
    public function show(string $room): Response
    {
        return $this->roomService->show($room);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CREATE A NEW LESSON
     * ES: ENDPOINT PARA CREAR UNA CLASE NUEVA
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/new', name: 'room_create', methods: ["GET", "POST"])]
    #[Permission(group: 'rooms', action: 'create')]
    public function new(): Response
    {
        return $this->roomService->new();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO EDIT A ROOM'S DATA
     * ES: ENDPOINT PARA EDITAR LOS DATOS DE UNA HABITACIÓN
     *
     * @param string $room
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/edit/{room}', name: 'room_edit', methods: ["GET",'POST'])]
    public function edit(string $room): Response
    {
        return $this->roomService->edit($room);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO DELETE A ROOM
     * ES: ENDPOINT PARA BORRAR UNA HABITACIÓN
     *
     * @param string $room
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/delete/{room}', name: 'room_delete', methods: ["GET",'POST'])]
    public function delete(string $room): Response
    {
        return $this->roomService->delete($room);
    }
    // ----------------------------------------------------------------
}