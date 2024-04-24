<?php

namespace App\Service\ClientService;

use App\Entity\Client\Client;
use App\Form\ClientType;
use App\Repository\BookingRepository;
use App\Repository\ClientRepository;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ClientService extends AbstractService
{

    const UPLOAD_FILES_PATH = 'images/clients';

    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly BookingRepository $bookingRepository,

        EntityManagerInterface $em,

        RouterInterface       $router,
        Environment           $twig,
        RequestStack          $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface      $tokenManager,
        FormFactoryInterface           $formFactory,
        SerializerInterface            $serializer,
        TranslatorInterface $translator,
        protected KernelInterface $kernel
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
            $this->clientRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL CLIENTS
     * ES: SERVICIO PARA LISTAR TODOS LOS CLIENTES
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {
        $clients = $this->clientRepository->findClients($this->filterService, false);

        return $this->render('client/index.html.twig', [
            'totalResults' => $clients['totalRegisters'],
            'lastPage' => $clients['lastPage'],
            'currentPage' => $clients['filters']['page'],
            'clients' => $clients['clients'],
            'filterService' => $this->filterService
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW A CLIENT'S DATA
     * ES: SERVICIO PARA MOSTRAR LOS DATOS DE UN CLIENTE
     *
     * @param string $clientId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function show(string $clientId): Response
    {
        $client = $this->clientRepository->findById($clientId, false);

        $this->filterService->addFilter('client', $client->getId());
        $bookings = $this->bookingRepository->findBookings($this->filterService, true);

        return $this->render('client/show.html.twig', [
            'currentPage' => $this->filterService->page,
            'client' => $client,
            'bookings' => $bookings['bookings'],
            'filterService' => $this->filterService,
            'lastPage' => $bookings['lastPage']
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CREATE A NEW CLIENT
     * ES: SERVICIO PARA CREAR UN CLIENTE NUEVO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function new(): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($form->get('password')->getData() != null) {
                    $this->clientRepository->upgradePassword($client, $form->get('password')->getData());
                }

                $this->getSession()->getFlashBag()->add('success', 'Cliente creado correctamente.');
                return $this->redirectToRoute('client_index');
            } catch (\Exception $error) {
                $this->getSession()->getFlashBag()->add('danger', 'Error al crear nuevo cliente.');
            }
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
            'edit' => false
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO EDIT A CLIENT
     * ES: SERVICIO PARA EDITAR UN CLIENTE
     *
     * @param string $clientId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function edit(string $clientId): Response
    {
        $client = $this->clientRepository->findById($clientId, false);
        $form = $this->createForm(ClientType::class, $client, ['edit' => true]);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($form->get('password')->getData() != null) {
                    $this->clientRepository->upgradePassword($client, $form->get('password')->getData());
                }

                $this->clientRepository->save($client, true);

                $this->getSession()->getFlashBag()->add('success', 'Cliente editado correctamente.');
                return $this->redirectToRoute('client_index');
            } catch (\Exception $error) {
                $this->getSession()->getFlashBag()->add('danger', 'Error al editar cliente.');
            }
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
            'edit' => true
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CHANGE A CLIENT'S STATUS
     * ES: SERVICIO PARA CAMBIAR EL ESTADO DE UN CLIENTE
     *
     * @param string $clientId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function change_status(string $clientId): Response
    {
        $client = $this->clientRepository->findById($clientId, false);

        try {
            if ($client->getStatus()) {
                $client->setStatus(false);

                foreach ($client->getBookings() as $booking) {
                    if ($booking->getSchedule()->getDateFrom() > UTCDateTime::create()) {
                        $this->bookingRepository->remove($booking, true);
                    }
                }
                $this->getSession()->getFlashBag()->add('success', 'Cliente desactivado correctamente.');
            } else {
                $client->setStatus(true);
                $this->getSession()->getFlashBag()->add('success', 'Cliente activado correctamente.');
            }

            $this->clientRepository->save($client, true);
        } catch (\Exception $error) {
            $this->getSession()->getFlashBag()->add('danger', 'Error al cambiar el estado del cliente');
        }

        return $this->redirectToRoute('client_index');
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO DELETE A CLIENT
     * ES: SERVICIO PARA BORRAR UN CLIENTE
     *
     * @param string $user
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function delete(string $clientId): Response
    {
        $client = $this->clientRepository->findById($clientId, false);

        try {
            if (count($client->getBookings()) == 0) {
                $this->clientRepository->remove($client, true);
                $this->getSession()->getFlashBag()->add('success', 'Cliente borrado correctamente.');
            } elseif ($client->getStatus()) {
                $this->getSession()->getFlashBag()->add('success', 'Se ha desactivado el cliente, ya que tiene reservas asignadas');

                $client->setStatus(false);
                foreach ($client->getBookings() as $booking) {
                    if ($booking->getSchedule()->getDateFrom() > UTCDateTime::create()) {
                        $this->bookingRepository->remove($booking, true);
                    }
                }

                $this->clientRepository->save($client, true);
            } else {
                $this->getSession()->getFlashBag()->add('error', 'El cliente desactivado tiene reservas asignadas, no se puede eliminar');
            }
        } catch (\Exception $error) {
            $this->getSession()->getFlashBag()->add('danger', 'Error al eliminar el cliente');
        }

        return $this->redirectToRoute('client_index');
    }
    // ----------------------------------------------------------------
}