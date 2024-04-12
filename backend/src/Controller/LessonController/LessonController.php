<?php

namespace App\Controller\LessonController;

use App\Annotation\Permission;
use App\Service\LessonService\LessonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/lessons')]
class LessonController extends AbstractController
{

    public function __construct(private readonly LessonService $lessonService)
    {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LIST ALL LESSONS
     * ES: ENDPOINT PARA LISTAR TODAS LAS CLASES
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/', name: 'lesson_index', methods: ["GET"])]
    #[Permission(group: 'lessons', action: 'list')]
    public function list(): Response
    {
        return $this->lessonService->index();
    }
    // ----------------------------------------------------------------
}