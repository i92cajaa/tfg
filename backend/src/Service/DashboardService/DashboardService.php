<?php
namespace App\Service\DashboardService;

use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\Config\ConfigType;
use App\Entity\User\User;
use App\Repository\AppointmentRepository;
use App\Repository\ClientRepository;
use App\Repository\ConfigRepository;
use App\Repository\ConfigTypeRepository;
use App\Repository\UserRepository;
use App\Service\ConfigService\ConfigService;
use App\Service\DocumentService\DocumentService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class DashboardService extends AbstractService
{
    const UPLOAD_FILES_PATH = 'configs';

    private ClientRepository $clientRepository;

    private AppointmentRepository $appointmentRepository;
    private UserRepository $userRepository;

    public function __construct(
        private readonly ConfigService $configService,

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
        $this->clientRepository = $em->getRepository(Client::class);
        $this->appointmentRepository = $em->getRepository(Appointment::class);
        $this->userRepository = $em->getRepository(User::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator
        );
    }

    public function index(): Response
    {
        $dateTo = UTCDateTime::create('NOW');
        $dateTo->format('Y-m-d H:i:s');

        $dateFrom = UTCDateTime::create('NOW');
        $dateFrom->modify('-31 days')->format('Y-m-d H:i:s');

        $clients = $this->clientRepository->getCount($dateFrom, $dateTo);
        $appointmentsCount = $this->appointmentRepository->getCount($dateFrom, $dateTo);
        $totalAmount = $this->appointmentRepository->getTotalPrice($dateFrom, $dateTo);
        $allAppointment = count($this->appointmentRepository->findAll());
        if($allAppointment > 0 && sizeof($appointmentsCount) > 0){
            $percent = (100 * $appointmentsCount[sizeof($appointmentsCount) - 1])/$allAppointment;
        } else{
            $percent = 0;
        }

        return $this->render('dashboard/index.html.twig', [
            'clientsCount' => $clients,
            'appointmentsCount' => $appointmentsCount,
            'allAppointments' => $allAppointment,
            'totalAmount' => $totalAmount,
            'percent' => $percent
        ]);
    }

    public function availables(): JsonResponse
    {
        if($this->getRequestPostParam('dateFrom') == '' || $this->getRequestPostParam('dateTo') == ''){
            return new JsonResponse([]);
        }else{
            $dateFrom = UTCDateTime::create('d-m-Y', $this->getRequestPostParam('dateFrom'))->setTime(0,0);
            $dateTo = UTCDateTime::create('d-m-Y', $this->getRequestPostParam('dateTo'))->setTime(23,0);
        }

        $clients = $this->clientRepository->getCount($dateFrom, $dateTo);
        $appointmentsCount = $this->appointmentRepository->getCount($dateFrom, $dateTo);
        $totalAmount = $this->appointmentRepository->getTotalPrice($dateFrom, $dateTo);
        $allAppointment = count($this->appointmentRepository->findAll());

        if($allAppointment > 0 && sizeof($appointmentsCount) > 0){
            $percent = (100 * $appointmentsCount[sizeof($appointmentsCount) - 1])/$allAppointment;
            $percent = number_format($percent,2);
        } else{
            $percent = 0;
        }

        $response = new JsonResponse(
            [
                'clientsCount' => $clients,
                'appointmentsCount' => $appointmentsCount,
                'totalAmount' => $totalAmount,
                'percent' => $percent
            ]
        );
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function toggleMode(): Response
    {
        $this->configService->toggleDarkMode();
        $user = $this->getUser();
        $newUser = $this->userRepository->find($user->getId());

        if($newUser->getDarkMode()){
            $newUser->setDarkMode(false);
        }else{
            $newUser->setDarkMode(true);
        }

        $this->userRepository->persist($newUser);

        return new Response("Modo guardado satisfactoriamente", 200);
    }

    public function toggleMenuExpanded(): Response
    {
        $this->configService->toggleMenuExpanded();
        $user = $this->getUser();
        $newUser = $this->userRepository->find($user->getId());

        if($newUser->getMenuExpanded()){
            $newUser->setMenuExpanded(false);
        }else{
            $newUser->setMenuExpanded(true);
        }

        $this->userRepository->persist($newUser);

        return new Response("Modo guardado satisfactoriamente", 200);
    }

}