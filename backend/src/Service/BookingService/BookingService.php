<?php

namespace App\Service\BookingService;

use App\Entity\Client\Booking;
use App\Entity\Status\Status;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\ClientRepository;
use App\Repository\LessonRepository;
use App\Repository\ScheduleRepository;
use App\Repository\StatusRepository;
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

class BookingService extends AbstractService
{

    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly ClientRepository $clientRepository,
        private readonly LessonRepository $lessonRepository,
        private readonly StatusRepository $statusRepository,
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
            entityRepository: $this->bookingRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL BOOKINGS INSIDE A
     * ES: SERVICIO PARA LISTAR TODAS LAS ÁREAS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {
        if ($this->getUser()->isAdmin() && !$this->getUser()->isSuperAdmin()) {
            $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
        }

        $bookings = $this->bookingRepository->findBookings($this->filterService, true);
        $lessons = $this->lessonRepository->findLessons($this->filterService, true)['lessons'];

        return $this->render('booking/index.html.twig', [
            'booking' => $bookings,  // Asegúrate de que esta variable esté definida
            'totalResults' => $bookings['totalRegisters'],
            'lastPage' => $bookings['lastPage'],
            'currentPage' => $bookings['filters']['page'],
            'bookings' => $bookings['bookings'],
            'filterService' => $this->filterService,
            'lessons' => $lessons
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW A BOOKING'S DATA
     * ES: SERVICIO PARA MOSTRAR LOS DATOS DE UNA RESERVA
     *
     * @param string $clientId
     * @param string $scheduleId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function show(string $clientId, string $scheduleId): Response
    {
        $booking = $this->bookingRepository->findByCompositeId(scheduleId: $scheduleId, clientId: $clientId, array: false);

        return $this->render('booking/show.html.twig', [
            'booking' => $booking
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CREATE A NEW BOOKING
     * ES: SERVICIO PARA CREAR UNA RESERVA NUEVA
     *
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function new(): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($booking->getSchedule()->getBookings()) === ($booking->getSchedule()->getRoom()->getCapacity() - 1)) {
                $booking->getSchedule()->setStatus($this->statusRepository->findById(Status::STATUS_FULL, false));

                $this->scheduleRepository->save($booking->getSchedule());
            }

            $this->bookingRepository->save($booking, true);

            return $this->redirectToRoute('booking_index');
        }

        $this->filterService->addFilter('status', true);
        if ($this->getUser()->isAdmin() && !$this->getUser()->isSuperAdmin()) {
            $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
        }

        return $this->render('booking/new.html.twig', [
            'booking' => $booking,
            'clients' => $this->clientRepository->findClients($this->filterService, true)['clients'],
            'lessons' => $this->lessonRepository->findLessons($this->filterService, true)['lessons'],
            'form' => $form->createView()
        ]);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO EDIT AN AREA'S DATA
     * ES: SERVICIO PARA EDITAR LOS DATOS DE UN ÁREA
     *
     * @param string $clientId
     * @param string $scheduleId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function edit(string $clientId, string $scheduleId): Response
    {
        $booking = $this->bookingRepository->findByCompositeId(
            scheduleId: $scheduleId,
            clientId: $clientId,
            array: false);

        $schedule = $booking->getSchedule();
        if ($schedule->getDateFrom() < UTCDateTime::create()->setTime(0,0)) {
            $this->getSession()->getFlashBag()->add('error', 'No se puede editar una reserva ya pasada.');
            return $this->redirectToRoute('booking_index');
        }

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            if ($booking->getSchedule()->getId() !== $schedule->getId()) {
                if ($schedule->getStatus()->getId() === Status::STATUS_COMPLETED) {
                    $schedule->setStatus($this->statusRepository->findById(Status::STATUS_AVAILABLE, false));

                    $this->scheduleRepository->save($schedule);
                }

                if (count($booking->getSchedule()->getBookings()) === ($booking->getSchedule()->getRoom()->getCapacity() - 1)) {
                    $booking->getSchedule()->setStatus($this->statusRepository->findById(Status::STATUS_FULL, false));

                    $this->scheduleRepository->save($booking->getSchedule());
                }
            }

            $this->bookingRepository->save($booking,true);

            return $this->redirectToRoute('booking_index');
        }

        $this->filterService->addFilter('status', true);
        if ($this->getUser()->isAdmin() && !$this->getUser()->isSuperAdmin()) {
            $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
        }

        return $this->render('booking/edit.html.twig', [
            'booking' => $booking,
            'clients' => $this->clientRepository->findClients($this->filterService, true)['clients'],
            'lessons' => $this->lessonRepository->findLessons($this->filterService, true)['lessons'],
            'form' => $form->createView()
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO DELETE A BOOKING
     * ES: SERVICIO PARA BORRAR UNA RESERVA
     *
     * @param string $clientId
     * @param string $scheduleId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function delete(string $clientId, string $scheduleId): Response
    {
        $booking = $this->bookingRepository->findByCompositeId(
            scheduleId: $scheduleId,
            clientId: $clientId,
            array: false);

        if ($booking->getSchedule()->getStatus()->getId() === Status::STATUS_FULL) {
            $booking->getSchedule()->setStatus($this->statusRepository->findById(Status::STATUS_AVAILABLE, false));
        }

        $this->bookingRepository->remove($booking,true);

        return $this->redirectToRoute('booking_index');
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE FOR A CLIENT TO BOOK A LESSON
     * ES: SERVICIO PARA QUE UN CLIENTE RESERVE UNA CLASE
     *
     * @param string $clientId
     * @param string $scheduleId
     * @return Response
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    // ----------------------------------------------------------------
    public function book(string $clientId, string $scheduleId): Response
    {

        $client = $this->clientRepository->findByIdSimple($clientId, false);
        if (!$client) {
            throw new \Exception('El cliente no existe en la base de datos');
        }

        $schedule = $this->scheduleRepository->findById($scheduleId, false);
        if (!$schedule) {
            throw new \Exception('El cliente no existe en la base de datos');
        }

        $booking = (new Booking())
            ->setClient($client)
            ->setSchedule($schedule);
        ;


        if (count($booking->getSchedule()->getBookings()) === ($booking->getSchedule()->getRoom()->getCapacity() - 1)) {
            $booking->getSchedule()->setStatus($this->statusRepository->findById(Status::STATUS_FULL, false));

            $this->scheduleRepository->save($booking->getSchedule());
        }

        $this->bookingRepository->save($booking, true);

        return new JsonResponse('DONE');

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO OBTAIN A CLIENT'S BOOKINGS' SCHEDULE ID
     * ES: SERVICIO PARA OBTENER EL ID DEL HORARIO DE LAS RESERVAS DE UN CLIENTE
     *
     * @param string $clientId
     * @return Response
     */
    // ----------------------------------------------------------------
    public function getSchedulesByClientsBookings(string $clientId): Response
    {

        $this->filterService->addFilter('client_id', $clientId);
        $bookings = $this->bookingRepository->findBookings($this->filterService, true)['bookings'];

        $filteredBookings = [];
        foreach ($bookings as $booking) {
            $filteredBookings[] = $booking->getSchedule()->getId();
        }

        return new JsonResponse($filteredBookings);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO OBTAIN A CLIENT'S BOOKINGS
     * ES: SERVICIO PARA OBTENER LAS RESERVAS DE UN CLIENTE
     *
     * @param string $clientId
     * @return Response
     */
    // ----------------------------------------------------------------
    public function getClientBookings(string $clientId): Response
    {

        $this->filterService->addFilter('client_id', $clientId);
        $bookings = $this->bookingRepository->findBookings($this->filterService, true)['bookings'];

        $filteredBookings = [];
        $return = [];
        foreach ($bookings as $booking) {
            $return[0][0]['name'] = $booking->getClient()->getFullName();
            $return[0][0]['dni'] = $booking->getClient()->getDni();
            $result = [];
            $result['schedule_id'] = $booking->getSchedule()->getId();
            $result['class'] = $booking->getSchedule()->getLesson()->getName();
            $result['room_floor'] = ($booking->getSchedule()->getRoom()->getFloor() == 0)? 'Baja' : $booking->getSchedule()->getRoom()->getFloor() . 'ª';
            $result['room_number'] = $booking->getSchedule()->getRoom()->getNumber();
            $result['day'] = $booking->getSchedule()->getDateFrom()->format('d/m');
            $result['date_from'] = $booking->getSchedule()->getDateFrom()->format('H:i');
            $result['date_to'] = $booking->getSchedule()->getDateTo()->format('H:i');
            $result['done'] = ($booking->getSchedule()->getDateFrom() >= UTCDateTime::create())? 1 : 0;
            $filteredBookings[] = $result;
        }

        $return[1] = $filteredBookings;

        return new JsonResponse($return);

    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CANCEL A CLIENT'S BOOKING
     * ES: SERVICIO PARA CANCELAR UNA RESERVA DE UN CLIENTE
     *
     * @param string $clientId
     * @param string $scheduleId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function appDelete(string $clientId, string $scheduleId): Response
    {
        $booking = $this->bookingRepository->findByCompositeId(
            scheduleId: $scheduleId,
            clientId: $clientId,
            array: false);

        if ($booking->getSchedule()->getStatus()->getId() === Status::STATUS_FULL) {
            $booking->getSchedule()->setStatus($this->statusRepository->findById(Status::STATUS_AVAILABLE, false));
        }

        $this->bookingRepository->remove($booking,true);

        return new JsonResponse('Done');
    }
    // ----------------------------------------------------------------
}