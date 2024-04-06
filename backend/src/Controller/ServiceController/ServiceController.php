<?php

namespace App\Controller\ServiceController;

use App\Entity\Service\Service;
use App\Repository\DivisionRepository;
use App\Repository\RoleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Service\FilterService;
use App\Service\ServiceService\ServiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;

#[Route(path: '/service')]
class ServiceController extends AbstractController
{

    public function __construct(
        private readonly ServiceService $serviceService
    )
    {
    }

    #[Route(path: '/', name: 'service_index', methods: ["GET"])]
    #[Permission(group: 'services', action:"list")]
    public function index(): Response
    {
        return $this->serviceService->index();
    }

    #[Route(path: '/get-by-date', name: 'service_get_by_date', methods: ["POST"])]
    public function getServicesByScheduleDates(): Response
    {
        return $this->serviceService->getServicesByScheduleDates();
    }

    #[Route(path: '/new', name: 'service_new', methods: ["GET","POST"])]
    #[Permission(group: 'services', action:"create")]
    public function new(): Response
    {
        return $this->serviceService->new();
    }

    #[Route(path: '/{service}', name: 'service_show', methods: ["GET"])]
    #[Permission(group: 'services', action:"show")]
    public function show(string $service): Response
    {
        return $this->serviceService->show($service);
    }

    #[Route(path: '/edit/{service}', name: 'service_edit', methods: ["GET","POST"])]
    #[Permission(group: 'services', action:"edit")]
    public function edit(string $service): Response
    {
        return $this->serviceService->edit($service);
    }

    #[Route(path: '/delete/{service}', name: 'service_delete', methods: ["POST"])]
    #[Permission(group: 'services', action:"delete")]
    public function delete(string $service): Response
    {
        return $this->serviceService->delete($service);
    }

    #[Route(path: '/available-by-client', name: 'service_get_by_client', methods: ["POST"])]
    public function getByClient(): Response
    {
        return $this->serviceService->getByClient();
    }
}
