<?php

namespace App\Service\LessonService;

use App\Repository\LessonRepository;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class LessonService extends AbstractService
{

    public function __construct(
        private readonly LessonRepository $lessonRepository,
        EntityManagerInterface $em,
        RouterInterface $router,
        Environment $twig,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface $tokenManager,
        FormFactoryInterface $formFactory,
        SerializerInterface $serializer,
        TranslatorInterface $translator
    )
    {
        parent::__construct(
            requestStack: $requestStack,
            router: $router,
            twig: $twig,
            tokenStorage: $tokenStorage,
            tokenManager: $tokenManager,
            formFactory: $formFactory,
            serializer: $serializer,
            translator: $translator,
            entityRepository: $this->lessonRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL LESSONS
     * ES: SERVICIO PARA LISTAR TODAS LAS CLASES
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {
        $lessons = $this->lessonRepository->findLessons($this->filterService, true);

        dd($lessons);

        return $this->render('lessons/index.html.twig', [
            'area' => $areas,  // Asegúrate de que esta variable esté definida
            'totalResults' => $areas['totalRegisters'],
            'lastPage' => $areas['lastPage'],
            'currentPage' => $this->filterService->page,
            'areas' => $areas['areas'],
            'filterService' => $this->filterService,
        ]);
    }
    // ----------------------------------------------------------------
}