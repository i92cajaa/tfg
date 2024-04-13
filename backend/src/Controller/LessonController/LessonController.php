<?php

namespace App\Controller\LessonController;

use App\Annotation\Permission;
use App\Service\LessonService\LessonService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/lesson')]
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

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW A LESSON'S DATA
     * ES: ENDPOINT PARA MOSTRAR LA INFORMACIÓN DE UNA CLASE
     *
     * @param string $lesson
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/show/{lesson}', name: 'lesson_show', methods: ["GET"])]
    #[Permission(group: 'lessons', action: 'show')]
    public function show(string $lesson): Response
    {
        return $this->lessonService->show($lesson);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CREATE A NEW LESSON
     * ES: ENDPOINT PARA CREAR UNA CLASE NUEVA
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/new', name: 'lesson_create', methods: ["GET", "POST"])]
    #[Permission(group: 'lessons', action: 'create')]
    public function new(): Response
    {
        return $this->lessonService->new();
    }
    // ----------------------------------------------------------------
}