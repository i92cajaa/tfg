<?php

namespace App\Controller\BookingController;

use App\Annotation\Permission;
use App\Service\BookingService\BookingService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/booking')]
class BookingController extends AbstractController
{

    public function __construct(private readonly BookingService $bookingService)
    {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LIST ALL AREAS
     * ES: ENDPOINT PARA LISTAR TODAS LAS ÁREAS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/', name: 'booking_index', methods: ["POST", "GET"])]
    #[Permission(group: 'bookings', action: 'list')]
    public function list(): Response
    {
        return $this->bookingService->index();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO SHOW A BOOKING'S DATA
     * ES: ENDPOINT PARA MOSTRAR LA INFORMACIÓN DE UNA RESERVA
     *
     * @param string $client
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/show/{client}/{schedule}', name: 'booking_show', methods: ["GET"])]
    #[Permission(group: 'bookings', action: 'show')]
    public function show(string $client, string $schedule): Response
    {
        return $this->bookingService->show(clientId: $client, scheduleId: $schedule);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CREATE A NEW BOOKING
     * ES: ENDPOINT PARA CREAR UNA RESERVA NUEVA
     *
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/new', name: 'booking_create', methods: ["GET", "POST"])]
    #[Permission(group: 'bookings', action: 'create')]
    public function new(): Response
    {
        return $this->bookingService->new();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO EDIT A BOOKING'S DATA
     * ES: ENDPOINT PARA EDITAR LOS DATOS DE UNA RESERVA
     *
     * @param string $client
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/edit/{client}/{schedule}', name: 'booking_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'bookings', action: 'edit')]
    public function edit(string $client, string $schedule): Response
    {
        return $this->bookingService->edit($client, $schedule);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO DELETE A BOOKING
     * ES: ENDPOINT PARA BORRAR UNA RESERVA
     *
     * @param string $client
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/delete/{client}/{schedule}', name: 'booking_delete', methods: ["GET", "POST"])]
    #[Permission(group: 'bookings', action: 'delete')]
    public function delete(string $client, string $schedule): Response
    {
        return $this->bookingService->delete($client, $schedule);
    }
    // ----------------------------------------------------------------
}