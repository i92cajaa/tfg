<?php

namespace App\Controller\ClientController;

use App\Entity\Client\Client;
use App\Service\ClientService\ClientService;
use App\Annotation\Permission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/client')]
class ClientController extends AbstractController
{

    public function __construct(
        private readonly ClientService $clientService

    )
    {
    }

    #[Route(path: '/', name: 'client_index', methods: ["GET"])]
    #[Permission(group: 'clients', action:"list")]
    public function index(): Response
    {
        return $this->clientService->index();
    }

    

    #[Route(path: '/new', name: 'client_new', methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action:"create")]
    public function new(): Response
    {
        return $this->clientService->new();
    }

    #[Route(path: '/{client}', name: 'client_show', defaults:["client" => null], methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action:"show")]
    public function show(string $client): Response
    {
        return $this->clientService->show($client);
    }

    
    

    #[Route(path: '/edit/{client}', name: 'client_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action:"edit")]
    public function edit(string $client): Response
    {
        return $this->clientService->edit($client);
    }

    #[Route(path: '/change-status/{client}', name: 'client_change_status', methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action:"edit")]
    public function changeStatus(string $client): Response
    {
        return $this->clientService->changeStatus($client);
    }

    #[Route(path: '/document/{client}', name: 'client_document', defaults: ["client" => null], methods: ["GET", "POST"])]
    #[Permission(group: 'clients', action:"show")]
    public function uploadDocument(string $client,Request $request): Response
    {
        return $this->clientService->uploadClientDocument($client,$request);
    }

    #[Route(path: '/delete/{client}', name: 'client_delete', methods: ["POST"])]
    #[Permission(group: 'clients', action:"delete")]
    public function delete(string $client, Request $request): Response
    {
        return $this->clientService->delete($client, $request);
    }
    #[Route(path: '/new/change_status_or_alumni', name: 'client_change_status_or_alumni', methods: ["GET", "POST"])]
    public function changeStatusOrAlumni(): Response
    {
        return $this->clientService->changeStatusOrAlumni();
    }
}
