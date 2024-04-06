<?php


namespace App\Controller\SurveyRangeController;


use App\Annotation\Permission;
use App\Entity\Document\Document;
use App\Service\SurveyRangeService\SurveyRangeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/survey_range')]
class SurveyRangeController extends AbstractController
{

    public function __construct(
        private readonly SurveyRangeService $surveyRangeService

    )
    {
    }

    #[Route(path: '/', name: 'survey_range_index', methods: ["GET"])]
    #[Permission(group: 'users', action: 'list')]
    public function index()
    {
        return $this->surveyRangeService->index();
    }

    #[Route(path: '/create', name: 'survey_range_create', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: 'create')]
    public function new()
    {
        return $this->surveyRangeService->new();
    }

    #[Route(path: '/edit/{surveyRange}', name: 'survey_range_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: 'edit')]
    public function edit(string $surveyRange)
    {
        return $this->surveyRangeService->edit($surveyRange);
    }

    #[Route(path: '/delete/{surveyRange}', name: 'survey_range_delete', methods: ["POST"])]
    #[Permission(group: 'users', action: 'delete')]
    public function delete(string $surveyRange)
    {
        return $this->surveyRangeService->delete($surveyRange);
    }
}