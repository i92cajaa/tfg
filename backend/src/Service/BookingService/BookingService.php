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
use App\Service\ScheduleService\ScheduleService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
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
use function Symfony\Component\Translation\t;

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
        $bookings = $this->bookingRepository->findBookings($this->filterService, true);

        return $this->render('booking/index.html.twig', [
            'booking' => $bookings,  // Asegúrate de que esta variable esté definida
            'totalResults' => $bookings['totalRegisters'],
            'lastPage' => $bookings['lastPage'],
            'currentPage' => $bookings['filters']['page'],
            'bookings' => $bookings['bookings'],
            'filterService' => $this->filterService,
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
                $booking->getSchedule()->setStatus($this->statusRepository->findById(Status::STATUS_COMPLETED, false));

                $this->scheduleRepository->save($booking->getSchedule());
            }

            $this->bookingRepository->save($booking, true);

            return $this->redirectToRoute('booking_index');
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
                    $booking->getSchedule()->setStatus($this->statusRepository->findById(Status::STATUS_COMPLETED, false));

                    $this->scheduleRepository->save($booking->getSchedule());
                }
            }

            $this->bookingRepository->save($booking,true);

            return $this->redirectToRoute('booking_index');
        }

        $this->filterService->addFilter('status', true);

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

        $this->bookingRepository->remove($booking,true);

        return $this->redirectToRoute('booking_index');
    }
    // ----------------------------------------------------------------
}