<?php

namespace App\Service\AreaService;

use App\Repository\AreaRepository;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AreaService extends AbstractService
{

    public function __construct(
        private readonly AreaRepository $areaRepository,
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
            entityRepository: $this->areaRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL AREAS
     * ES: SERVICIO PARA LISTAR TODAS LAS ÁREAS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {
        $areas = $this->areaRepository->findAreas($this->filterService, true);

        dd($areas);

        return $this->render('areas/index.html.twig', [
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