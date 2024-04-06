<?php

namespace App\Controller\CenterController;

use App\Annotation\Permission;
use App\Service\CenterService\CenterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/center')]
class CenterController extends AbstractController
{
    public function __construct(private readonly CenterService $centerService)
    {
    }

    #[Route(path: '/', name: 'center_index', methods: ["GET"])]
    #[Permission(group: 'appointments', action:"list")]
    public function index(): Response
    {

        return $this->centerService->index();
    }

    #[Route(path: '/new', name: 'center_new', methods: ["GET",'POST'])]
    #[Permission(group: 'appointments', action:"list")]
    public function new(): Response
    {
        return $this->centerService->new();
    }
    #[Route(path: '/show/{center}', name: 'center_show', methods: ["GET",'POST'])]

    public function show(string $center): Response
    {
        return $this->centerService->show($center);
    }


    #[Route(path: '/delete/{center}', name: 'center_delete', methods: ["GET",'POST'])]
    public function delete(string $center): Response
    {
        return $this->centerService->delete($center);
    }

    #[Route(path: '/edit/{center}', name: 'center_edit', methods: ["GET",'POST'])]
    public function edit(string $center): Response
    {
        return $this->centerService->edit($center);
    }
}