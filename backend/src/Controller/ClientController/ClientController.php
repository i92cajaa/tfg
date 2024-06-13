<?php

namespace App\Controller\ClientController;

use App\Annotation\Permission;
use App\Service\CenterService\CenterService;
use App\Service\ClientService\ClientService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/client')]
class ClientController extends AbstractController
{

    public function __construct(
        private readonly ClientService $clientService,
        private readonly CenterService $centerService,
    ) {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LIST ALL CLIENTS
     * ES: ENDPOINT PARA LISTAR TODOS LOS CLIENTES
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/', name: 'client_index', methods: ["GET"])]
    #[Permission(group: 'clients', action: "list")]
    public function index(): Response
    {
        return $this->clientService->index();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW A CLIENT'S DATA
     * ES: ENDPOINT PARA MOSTRAR LOS DATOS DE UN CLIENTE
     *
     * @param string $client
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/show/{client}', name: 'client_show', methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action: "show")]
    public function show(string $client): Response
    {
        return $this->clientService->show($client);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CREATE A NEW CLIENT
     * ES: ENDPOINT PARA CREAR UN CLIENTE NUEVO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/new', name: 'client_new', methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action: "create")]
    public function new(): Response
    {
        return $this->clientService->new();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO EDIT A CLIENT
     * ES: ENDPOINT PARA EDITAR UN CLIENTE
     *
     * @param string $client
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/edit/{client}', name: 'client_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action: "edit")]
    public function edit(string $client): Response
    {
        return $this->clientService->edit($client);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CHANGE A CLIENT'S STATUS
     * ES: ENDPOINT PARA CAMBIAR EL ESTADO DE UN CLIENTE
     *
     * @param string $client
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/change-status/{client}', name: 'client_change_status', methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action: "edit")]
    public function change_status(string $client): Response
    {
        return $this->clientService->change_status($client);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO DELETE A CLIENT
     * ES: ENDPOINT PARA BORRAR UN CLIENTE
     *
     * @param string $client
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/delete/{client}', name: 'client_delete', methods: ["POST"])]
    #[Permission(group: 'clients', action: "delete")]
    public function delete(string $client): Response
    {
        return $this->clientService->delete($client);
    }
    // ----------------------------------------------------------------
}