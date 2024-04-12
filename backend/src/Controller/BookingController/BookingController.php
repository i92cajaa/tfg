<?php

namespace App\Controller\BookingController;

use App\Annotation\Permission;
use App\Service\BookingService\BookingService;
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
}