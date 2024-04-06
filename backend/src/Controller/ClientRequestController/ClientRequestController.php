<?php

namespace App\Controller\ClientRequestController;

use App\Entity\ClientRequest\ClientRequest;
use App\Service\ClientRequestService\ClientRequestService;
use App\Annotation\Permission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/request')]
class ClientRequestController extends AbstractController
{

    public function __construct(
        private readonly ClientRequestService $clientRequestService

    )
    {
    }

    #[Route(path: '/', name: 'client_request_index', methods: ["GET"])]
    #[Permission(group: 'requests', action:"list")]
    public function index(): Response
    {
        return $this->clientRequestService->index();
    }


    #[Route(path: '/{clientRequest}', name: 'client_request_show', defaults:["clientRequest" => null], methods: ["GET", "POST"])]
    #[Permission(group: 'requests', action:"show")]
    public function show(string $clientRequest): Response
    {
        return $this->clientRequestService->show($clientRequest);
    }

    #[Route(path: '/change-status/{clientRequest}', name: 'client_request_change_status', defaults:["clientRequest" => null], methods: ["GET","POST"])]
    #[Permission(group: 'requests', action:"change_status")]
    public function editStatus(string $clientRequest): Response
    {
        return $this->clientRequestService->editStatus($clientRequest);
    }

    #[Route(path: '/validate/{clientRequest}', name: 'client_request_validate', defaults:["clientRequest" => null], methods: ["GET","POST"])]
    #[Permission(group: 'requests', action:"validate")]
    public function validate(string $clientRequest): Response
    {
        return $this->clientRequestService->validate($clientRequest);
    }

    #[Route(path: '/edit/{clientRequest}', name: 'client_request_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'requests', action:"edit")]
    public function edit(string $clientRequest): Response
    {
        return $this->clientRequestService->edit($clientRequest);
    }

    #[Route(path: '/delete/{clientRequest}', name: 'client_request_delete', methods: ["POST"])]
    #[Permission(group: 'requests', action:"delete")]
    public function delete(string $clientRequest): Response
    {
        return $this->clientRequestService->delete($clientRequest);
    }
}
