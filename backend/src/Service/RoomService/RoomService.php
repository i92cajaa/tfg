<?php

namespace App\Service\RoomService;

use App\Entity\Room\Room;
use App\Form\RoomType;
use App\Repository\CenterRepository;
use App\Repository\RoomRepository;
use App\Shared\Classes\AbstractService;
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

class RoomService extends AbstractService
{

    public function __construct(
        private readonly RoomRepository $roomRepository,
        private readonly CenterRepository $centerRepository,
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
            entityRepository: $this->roomRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL ROOMS
     * ES: SERVICIO PARA LISTAR TODAS LAS HABITACIONES
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {
        $rooms = $this->roomRepository->findRooms($this->filterService, false);

        return $this->render('room/index.html.twig', [
            'room' => $rooms,  // Asegúrate de que esta variable esté definida
            'totalResults' => $rooms['totalRegisters'],
            'lastPage' => $rooms['lastPage'],
            'currentPage' => $rooms['filters']['page'],
            'rooms' => $rooms['rooms'],
            'filterService' => $this->filterService,
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO GET ALL ROOMS BY USER ID
     * ES: SERVICIO PARA OBTENER TODAS LAS HABITACIONES POR ID DE USUARIO
     *
     * @return JsonResponse
     */
    // ----------------------------------------------------------------
    public function getByUserId(): JsonResponse
    {
        $rooms = [];
        $status = false;
        if ($this->isCsrfTokenValid('get-rooms-by-user', $this->getRequestPostParam('_token'))) {
            $this->filterService->addFilter('user', $this->getRequestPostParam('user'));
            $center = $this->centerRepository->findCenters($this->filterService, true)['centers'][0];

            $this->filterService->addFilter('center', $center->getId());

            $rooms = $this->roomRepository->findRooms($this->filterService, true, true)['rooms'];
            $status = true;
        }

        return new JsonResponse(['rooms' => $rooms, 'status' => $status]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW A ROOM'S DATA
     * ES: SERVICIO PARA MOSTRAR LOS DATOS DE UNA HABITACIÓN
     *
     * @param string $roomId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function show(string $roomId): Response
    {
        $room = $this->roomRepository->findById($roomId, false);

        return $this->render('room/show.html.twig', [
            'room' => $room
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CREATE A NEW ROOM
     * ES: SERVICIO PARA CREAR UNA HABITACIÓN NUEVA
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function new():Response{
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $this->roomRepository->save($room,true);

            return $this->redirectToRoute('room_index');
        }

        $centers = $this->centerRepository->findCenters($this->filterService, true);

        return $this->render('room/new.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
            'centers' => $centers['centers']
        ]);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO EDIT A ROOM'S DATA
     * ES: SERVICIO PARA EDITAR LOS DATOS DE UN CENTRO
     *
     * @param string $roomId
     * @return Response
     */
    // ----------------------------------------------------------------
    public function edit(string $roomId): Response
    {
        $room = $this->getEntity($roomId);

        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $this->roomRepository->save($room,true);

            return $this->redirectToRoute('room_index');
        }

        $centers = $this->centerRepository->findCenters($this->filterService, true);

        return $this->render('room/edit.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
            'centers' => $centers['centers']
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO DELETE A ROOM
     * ES: SERVICIO PARA BORRAR UNA HABITACIÓN
     *
     * @param string $roomId
     * @return Response
     */
    // ----------------------------------------------------------------
    public function delete(string $roomId): Response
    {
        $room = $this->getEntity($roomId);
        $this->roomRepository->remove($room,true);

        return $this->redirectToRoute('room_index');
    }
    // ----------------------------------------------------------------
}