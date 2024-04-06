<?php

namespace App\Controller\TemplateController;

use App\Entity\Template\TemplateType;
use App\Repository\TemplateTypeRepository;
use App\Service\TemplateService\TemplateTypeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;


#[Route(path: '/template/type')]
class TemplateTypeController extends AbstractController
{

    public function __construct(
        private readonly TemplateTypeService $templateTypeService
    )
    {
    }

    #[Route(path: '/', name: 'template_type_index', methods: ["GET"])]
    #[Permission(group: 'template_types', action:"list")]
    public function index(): Response
    {
        return $this->templateTypeService->index();
    }

    #[Route(path: '/new', name: 'template_type_new', methods: ["GET", "POST"])]
    #[Permission(group: 'template_types', action:"create")]
    public function new(): Response
    {
        return $this->templateTypeService->create();
    }

    #[Route(path: '/edit/{templateType}', name: 'template_type_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'template_types', action:"edit")]
    public function edit(string $templateType): Response
    {
        return $this->templateTypeService->edit($templateType);
    }

    #[Route(path: '/delete/{templateType}', name: 'template_type_delete', methods: ["POST"])]
    #[Permission(group: 'template_types', action:"edit")]
    public function delete(string $templateType): Response
    {
        return $this->templateTypeService->delete($templateType);
    }
}
