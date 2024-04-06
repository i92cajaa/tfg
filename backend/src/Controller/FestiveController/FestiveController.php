<?php

namespace App\Controller\FestiveController;

use App\Entity\Festive\Festive;
use App\Repository\FestiveRepository;
use App\Service\FestiveService\FestiveService;
use App\Service\FilterService;
use App\Annotation\Permission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/festive')]
class FestiveController extends AbstractController
{

    public function __construct(
        private readonly FestiveService $festiveService
    )
    {
    }

    #[Route(path: '/', name: 'festive_index', methods: ["GET"])]
    #[Permission(group: 'festives', action:"list")]
    public function index(): Response
    {
        return $this->festiveService->index();
    }

    #[Route(path: '/new', name: 'festive_new', methods: ["GET","POST"])]
    #[Permission(group: 'festives', action:"create")]
    public function new(): Response
    {
        return $this->festiveService->new();
    }

    #[Route(path: '/{festive}', name: 'festive_show', methods: ["GET"])]
    #[Permission(group: 'festives', action:"show")]
    public function show(string $festive): Response
    {
        return $this->festiveService->show($festive);
    }

    #[Route(path: '/edit/{festive}', name: 'festive_edit', methods: ["GET","POST"])]
    #[Permission(group: 'festives', action:"edit")]
    public function edit(string $festive): Response
    {
        return $this->festiveService->edit($festive);
    }

    #[Route(path: '/delete/{festive}', name: 'festive_delete', methods: ["POST"])]
    #[Permission(group: 'festives', action:"delete")]
    public function delete(string $festive): Response
    {
        return $this->festiveService->delete($festive);
    }
}
