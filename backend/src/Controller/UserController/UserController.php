<?php

namespace App\Controller\UserController;

use App\Annotation\Permission;
use App\Service\UserService\UserService;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/user')]
class UserController extends AbstractController
{

    public function __construct(
        private readonly UserService $userService
    ) {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LIST ALL USERS
     * ES: ENDPOINT PARA LISTAR TODOS LOS USUARIOS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/', name: 'user_index', methods: ["GET"])]
    #[Permission(group: 'users', action: "list")]
    public function index(): Response
    {
        return $this->userService->index();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO GET ALL TEACHERS BY THE CENTER
     * ES: ENDPOINT PARA OBTENER LOS PROFESORES DE UN CENTRO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/users_by_center', name: 'user_get_by_center', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "list")]
    public function getByCenter(): Response
    {
        return $this->userService->getByCenter();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW AN USER'S DATA
     * ES: ENDPOINT PARA MOSTRAR LOS DATOS DE UN USUARIO
     *
     * @param string $user
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/show/{user}', name: 'user_show', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "show")]
    public function show(string $user): Response
    {
        return $this->userService->show($user);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW AN USER'S PROFILE
     * ES: ENDPOINT PARA MOSTRAR EL PERFIL DE UN USUARIO
     *
     * @param string $user
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/view_profile/{user}', name: 'user_view_profile', methods: ["GET", "POST"])]
    public function user_view_profile(string $user, Request $request): Response
    {
        return $this->userService->user_view_profile($user, $request);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CREATE A NEW USER
     * ES: ENDPOINT PARA CREAR UN USUARIO NUEVO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/new', name: 'user_new', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "create")]
    public function new(): Response
    {
        return $this->userService->new();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO EDIT AN USER
     * ES: ENDPOINT PARA EDITAR UN USUARIO
     *
     * @param string $user
     * @return Response
     * @throws Exception
     */
    // ----------------------------------------------------------------
    #[Route(path: '/edit/{user}', name: 'user_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "edit")]
    public function edit(string $user): Response
    {
        return $this->userService->edit($user);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CHANGE AN USER'S STATUS
     * ES: ENDPOINT PARA CAMBIAR EL ESTADO DE UN USUARIO
     *
     * @param string $user
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/change-status/{user}', name: 'user_change_status', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "edit")]
    public function change_status(string $user): Response
    {
        return $this->userService->change_status($user);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO DELETER AN USER
     * ES: ENDPOINT PARA BORRAR UN USUARIO
     *
     * @param string $user
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/delete/{user}', name: 'user_delete', methods: ["POST"])]
    #[Permission(group: 'users', action: "delete")]
    public function delete(string $user): Response
    {
        return $this->userService->delete($user);
    }
    // ----------------------------------------------------------------
}
