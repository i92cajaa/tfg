<?php

namespace App\Service\LessonService;

use App\Entity\Lesson\Lesson;
use App\Form\LessonType;
use App\Repository\CenterRepository;
use App\Repository\LessonRepository;
use App\Repository\UserRepository;
use App\Service\DocumentService\DocumentService;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
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
        private readonly UserRepository $userRepository,
        private readonly CenterRepository $centerRepository,
        private readonly DocumentService $documentService,
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
        $lessons = $this->lessonRepository->findLessons($this->filterService, false);

        return $this->render('lesson/index.html.twig', [
            'lesson' => $lessons,  // Asegúrate de que esta variable esté definida
            'totalResults' => $lessons['totalRegisters'],
            'lastPage' => $lessons['lastPage'],
            'currentPage' => $lessons['filters']['page'],
            'lessons' => $lessons['lessons'],
            'filterService' => $this->filterService,
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW A LESSON'S DATA
     * ES: SERVICIO PARA MOSTRAR LOS DATOS DE UNA CLASE
     *
     * @param string $lessonId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function show(string $lessonId): Response
    {
        $lesson = $this->lessonRepository->findById($lessonId, false);

        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CREATE A NEW LESSON
     * ES: SERVICIO PARA CREAR UNA CLASE NUEVA
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function new():Response{
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('image')->getData();
            if($file != null){
                $img = $this->documentService->uploadDocument($file, 'lesson');
                $lesson->setImage($img);
            }

            $this->lessonRepository->save($lesson,true);

            return $this->redirectToRoute('lesson_index');
        }

        $centers = $this->centerRepository->findCenters($this->filterService, true);

        $this->filterService->addFilter('roles', 3);
        $users = $this->userRepository->findUsers($this->filterService, true);

        return $this->render('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form->createView(),
            'users' => $users['users'],
            'centers' => $centers['centers']
        ]);

    }
    // ----------------------------------------------------------------
}