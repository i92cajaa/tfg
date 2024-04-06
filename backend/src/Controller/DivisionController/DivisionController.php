<?php

namespace App\Controller\DivisionController;

use App\Entity\Service\Division;
use App\Repository\DivisionRepository;
use App\Service\DivisionService\DivisionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;


#[Route(path: '/division')]
class DivisionController extends AbstractController
{

    public function __construct(
        private readonly DivisionService $divisionService

    )
    {
    }

    #[Route(path: '/new', name: 'division_new', methods: ["GET", "POST"])]
    #[Permission(group: 'services', action:"manage_divisions")]
    public function new(): Response
    {
        return $this->divisionService->new();
    }

    #[Route(path: '/edit/{division}', name: 'division_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'services', action:"manage_divisions")]
    public function edit(string $division): Response
    {
        return $this->divisionService->edit($division);
    }

    #[Route(path: '/delete/{division}', name: 'division_delete', methods: ["POST"])]
    #[Permission(group: 'services', action:"manage_divisions")]
    public function delete(string $division): Response
    {
        return $this->divisionService->delete($division);
    }
}
