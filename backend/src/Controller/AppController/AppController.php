<?php

namespace App\Controller\AppController;

use App\Service\BookingService\BookingService;
use App\Service\CenterService\CenterService;
use App\Service\LessonService\LessonService;
use App\Service\ScheduleService\ScheduleService;
use App\Service\SecurityService\SecurityService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/app')]
class AppController extends AbstractController
{

    public function __construct(
        private readonly SecurityService $securityService,
        private readonly CenterService $centerService,
        private readonly LessonService $lessonService,
        private readonly ScheduleService $scheduleService,
        private readonly BookingService $bookingService
    )
    {
    }

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO LOGIN WITH A CLIENT
     * ES: ENDPOINT PARA LOGUEAR CON UN CLIENTE
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/login', name: 'app_login_client')]
    public function clientLogin(): Response
    {
        return $this->securityService->clientLogin();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO OBTAIN ALL CENTERS' INFO
     * ES: ENDPOINT PARA OBTENER TODA LA INFORMACIÓN DE LOS CENTROS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/all-centers', name: 'app_client_index')]
    public function getAllCenters(): Response
    {
        return $this->centerService->appGetCenters();
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO OBTAIN A CENTER'S LESSONS
     * ES: ENDPOINT PARA OBTENER TODAS LAS CLASES DE UN CENTRO
     *
     * @param string $center
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/lessons-by-center/{center}', name: 'app_lessons_by_center')]
    public function getAllLessonsByCenterId(string $center): Response
    {
        return $this->lessonService->appGetLessonsByCenterId($center);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO OBTAIN A LESSON'S SCHEDULES
     * ES: ENDPOINT PARA OBTENER TODOS LOS HORARIOS DE UNA CLASE
     *
     * @param string $lesson
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/schedules-by-lesson/{lesson}', name: 'app_schedules_by_lesson')]
    public function getAllSchedulesByLessonId(string $lesson): Response
    {
        return $this->scheduleService->appGetSchedulesByLessonId($lesson);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT FOR A CLIENT TO BOOK A LESSON
     * ES: ENDPOINT PARA QUE UN CLIENTE RESERVE UNA CLASE
     *
     * @param string $client
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/book/{client}/{schedule}', name: 'app_book')]
    public function book(string $client, string $schedule): Response
    {
        return $this->bookingService->book($client, $schedule);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO OBTAIN A CLIENT'S BOOKINGS' SCHEDULE ID
     * ES: ENDPOINT PARA OBTENER EL ID DEL HORARIO DE LAS RESERVAS DE UN CLIENTE
     *
     * @param string $client
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/bookings-schedules-id-by-client/{client}', name: 'app_bookings_schedule_id_by_client')]
    public function getBookingsScheduleIds(string $client): Response
    {
        return $this->bookingService->getSchedulesByClientsBookings($client);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO OBTAIN A CLIENT'S BOOKINGS
     * ES: ENDPOINT PARA OBTENER LAS RESERVAS DE UN CLIENTE
     *
     * @param string $client
     * @return Response
     */
    // ----------------------------------------------------------------
    #[Route(path: '/bookings-by-client/{client}', name: 'app_bookings_by_client')]
    public function getBookings(string $client): Response
    {
        return $this->bookingService->getClientBookings($client);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: ENDPOINT TO CANCEL A CLIENT'S BOOKING
     * ES: ENDPOINT PARA CANCELAR LA RESERVA DE UN CLIENTE
     *
     * @param string $client
     * @param string $schedule
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    #[Route(path: '/cancel-booking/{client}/{schedule}', name: 'app_cancel_booking')]
    public function cancelBooking(string $client, string $schedule): Response
    {
        return $this->bookingService->appDelete($client, $schedule);
    }
    // ----------------------------------------------------------------


}