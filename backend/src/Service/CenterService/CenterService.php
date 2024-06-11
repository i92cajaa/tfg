<?php

namespace App\Service\CenterService;


use App\Entity\Center\Center;
use App\Form\CenterType;
use App\Repository\AreaRepository;
use App\Repository\CenterRepository;
use App\Service\DocumentService\DocumentService;
use App\Service\FilterService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CenterService extends AbstractService
{

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly CenterRepository $centerRepository,
        private readonly AreaRepository $areaRepository,
        EntityManagerInterface $em,
        RouterInterface       $router,
        Environment           $twig,
        RequestStack          $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface      $tokenManager,
        FormFactoryInterface           $formFactory,
        SerializerInterface            $serializer,
        TranslatorInterface $translator
        )
    {
        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->centerRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL CENTERS
     * ES: SERVICIO PARA LISTAR TODOS LOS CENTROS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {

        $centers = $this->centerRepository->findCenters($this->filterService, true);

        return $this->render('center/index.html.twig', [
            'center' => $centers,
            'totalResults' => $centers['totalRegisters'],
            'lastPage' => 0,
            'currentPage' => $this->filterService->page,
            'centers' => $centers['centers'],
            'filterService' => $this->filterService,
            'areas' => $this->areaRepository->findAll()
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW A CENTER'S DATA
     * ES: SERVICIO PARA MOSTRAR LOS DATOS DE UN CENTRO
     *
     * @param string $centerId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function show(string $centerId): Response
    {
        $center = $this->centerRepository->findById($centerId, false);

        return $this->render('center/show.html.twig', [
            'center' => $center
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CREATE A NEW CENTER
     * ES: SERVICIO PARA CREAR UN CENTRO NUEVO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function new():Response{
        $center = new Center();
        $form = $this->createForm(CenterType::class, $center);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() &&
            $this->getRequestPostParam('center')['opening_time'] != null &&
            $this->getRequestPostParam('center')['closing_time'] != null)
        {

            $center->setOpeningTime(UTCDateTime::create('H:i', $form->get('opening_time')->getViewData()));
            $center->setClosingTime(UTCDateTime::create('H:i', $form->get('closing_time')->getViewData()));

            $file = $form->get('logo')->getData();
            if($file != null){
                $imgProfile = $this->documentService->uploadDocument($file, 'center');
                $center->setLogo($imgProfile);
            }

            $this->centerRepository->save($center,true);

            return $this->redirectToRoute('center_index');
        }

        $areas = $this->areaRepository->findAreas($this->filterService, true);

        return $this->render('center/new.html.twig', [
            'center' => $center,
            'form' => $form->createView(),
            'areas' => $areas['areas']
        ]);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO EDIT A CENTER'S DATA
     * ES: SERVICIO PARA EDITAR LOS DATOS DE UN CENTRO
     *
     * @param string $centerId
     * @return Response
     */
    // ----------------------------------------------------------------
    public function edit(string $centerId): Response
    {
        $center = $this->getEntity($centerId);

        $form = $this->createForm(CenterType::class, $center);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() &&
            $this->getRequestPostParam('center')['opening_time'] != null &&
            $this->getRequestPostParam('center')['closing_time'] != null)
        {

            $center->setOpeningTime(UTCDateTime::create('H:i', $form->get('opening_time')->getViewData()));
            $center->setClosingTime(UTCDateTime::create('H:i', $form->get('closing_time')->getViewData()));

            $file = $form->get('logo')->getData();
            if($file != null){
                $imgProfile = $this->documentService->uploadDocument($file, 'center');
                $center->setLogo($imgProfile);
            }

            $this->centerRepository->save($center,true);

            return $this->redirectToRoute('center_index');
        }

        $areas = $this->areaRepository->findAreas($this->filterService, true);

        return $this->render('center/edit.html.twig', [
            'center' => $center,
            'form' => $form->createView(),
            'areas' => $areas['areas']
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO DELETE A CENTER
     * ES: SERVICIO PARA BORRAR UN CENTRO
     *
     * @param string $centerId
     * @return Response
     */
    // ----------------------------------------------------------------
    public function delete(string $centerId): Response
    {
        $center = $this->getEntity($centerId);
        $this->centerRepository->remove($center,true);

        return $this->redirectToRoute('center_index');
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST CENTERS FOR THE APP
     * ES: SERVICIO PARA LISTAR LOS CENTROS PARA LA APP
     *
     * @return JsonResponse
     */
    // ----------------------------------------------------------------
    public function appGetCenters(): JsonResponse
    {
        return new JsonResponse(
            data: $this->centerRepository->findCenters($this->filterService, true)['centers'],
            status: 200,
            json: true
        );
    }
    // ----------------------------------------------------------------
}