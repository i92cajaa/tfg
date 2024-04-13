<?php

namespace App\Service\RoomService;

use App\Repository\RoomRepository;
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

class RoomService extends AbstractService
{

    public function __construct(
        private readonly RoomRepository $roomRepository,
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
}