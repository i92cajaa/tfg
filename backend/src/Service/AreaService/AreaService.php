<?php

namespace App\Service\AreaService;

use App\Entity\Area\Area;
use App\Form\AreaType;
use App\Repository\AreaRepository;
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

        return $this->render('area/index.html.twig', [
            'area' => $areas,  // Asegúrate de que esta variable esté definida
            'totalResults' => $areas['totalRegisters'],
            'lastPage' => $areas['lastPage'],
            'currentPage' => $this->filterService->page,
            'areas' => $areas['areas'],
            'filterService' => $this->filterService,
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW AN AREA'S DATA
     * ES: SERVICIO PARA MOSTRAR LOS DATOS DE UN ÁREA
     *
     * @param string $areaId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function show(string $areaId): Response
    {
        $area = $this->areaRepository->findById($areaId, false);

        return $this->render('area/show.html.twig', [
            'area' => $area
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CREATE A NEW AREA
     * ES: SERVICIO PARA CREAR UN ÁREA NUEVA
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function new(): Response
    {
        $area = new Area();
        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->areaRepository->save($area, true);

            return $this->redirectToRoute('area_index');
        }

        return $this->render('area/new.html.twig', [
            'area' => $area,
            'form' => $form->createView(),
        ]);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO EDIT AN AREA'S DATA
     * ES: SERVICIO PARA EDITAR LOS DATOS DE UN ÁREA
     *
     * @param string $area
     * @return Response
     */
    // ----------------------------------------------------------------
    public function edit(string $area): Response
    {
        $area = $this->getEntity($area);

        $form = $this->createForm(AreaType::class, $area);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->areaRepository->save($area,true);

            return $this->redirectToRoute('area_index');
        }

        return $this->render('area/edit.html.twig', [
            'area' => $area,
            'form' => $form->createView()
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO DELETE AN AREA
     * ES: SERVICIO PARA BORRAR UN ÁREA
     *
     * @param string $area
     * @return Response
     */
    // ----------------------------------------------------------------
    public function delete(string $area): Response
    {
        $area = $this->getEntity($area);
        $this->areaRepository->remove($area,true);

        return $this->redirectToRoute('area_index');
    }
    // ----------------------------------------------------------------
}