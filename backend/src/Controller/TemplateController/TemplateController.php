<?php

namespace App\Controller\TemplateController;


use App\Entity\Template\Template;
use App\Service\TemplateService\TemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;


#[Route(path: '/template')]
class TemplateController extends AbstractController
{

    public function __construct(
        private readonly TemplateService $templateService
    )
    {
    }

    #[Route(path: '/get-create-template', name: 'template_get_create_template', methods: ["POST"])]
    #[Permission(group: 'templates', action:"create")]
    public function getCreateTemplateTemplate(): Response
    {
        return $this->templateService->getCreateTemplateTemplate();
    }

    #[Route(path: '/new', name: 'template_new', methods: ["POST"])]
    #[Permission(group: 'templates', action:"create")]
    public function new(): Response
    {
        return $this->templateService->createTemplateByRequest();

    }

    #[Route(path: '/get-edit-template', name: 'template_get_edit_template', methods: ["GET"])]
    #[Permission(group: 'templates', action:"edit")]
    public function getEditTemplateTemplate(): Response
    {
        return $this->templateService->getEditTemplateTemplate();
    }

    #[Route(path: '/edit', name: 'template_edit', methods: ["POST"])]
    #[Permission(group: 'templates', action:"edit")]
    public function edit(): Response
    {
        return $this->templateService->editTemplatesByRequest();
    }

    #[Route(path: '/delete/{template}', name: 'template_delete', methods: ["POST"])]
    #[Permission(group: 'templates', action:"delete")]
    public function delete(string $template): Response
    {
        return $this->templateService->deleteTemplate($template);
    }

    #[Route(path: '/pdf', name: 'export_template_pdf', methods: ["POST"])]
    #[Permission(group: 'templates', action:"export")]
    public function pdfAction()
    {
        return $this->templateService->exportPdf();
    }
    
}