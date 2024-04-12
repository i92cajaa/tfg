<?php

namespace App\Service\ScheduleService;

use App\Repository\ScheduleRepository;
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

class ScheduleService extends AbstractService
{

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository,
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
            entityRepository: $this->scheduleRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL SCHEDULES INSIDE A CALENDAR
     * ES: SERVICIO PARA LISTAR TODOS LOS HORARIOS DENTRO DE UN CALENDARIO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {
        $schedules = $this->scheduleRepository->findSchedules($this->filterService, true);

        dd($schedules);

        return $this->render('schedules/index.html.twig', [
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