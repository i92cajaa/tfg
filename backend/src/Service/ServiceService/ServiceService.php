<?php


namespace App\Service\ServiceService;


use App\Entity\Role\Role;
use App\Entity\Role\RoleHasPermission;
use App\Entity\Service\Division;
use App\Entity\Service\Service;
use App\Entity\User\User;
use App\Form\PaymentMethodType;
use App\Form\ServiceType;
use App\Repository\DivisionRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\RoleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ServiceService extends AbstractService
{

    /**
     * @var ServiceRepository
     */
    private ServiceRepository $serviceRepository;
    /**
     * @var DivisionRepository
     */
    private DivisionRepository $divisionRepository;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;


    public function __construct(
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

        $this->serviceRepository = $em->getRepository(Service::class);
        $this->divisionRepository = $em->getRepository(Division::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->roleRepository = $em->getRepository(Role::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->serviceRepository
        );
    }

    public function getServicesByScheduleDates(): JsonResponse
    {
        $services = [];
        if ($this->isCsrfTokenValid('get-services-by-dates', $this->getRequestPostParam('_token'))) {
            $date = UTCDateTime::create('Y-m-d', $this->getRequestPostParam('date'), new \DateTimeZone('UTC'));
            $clientId = $this->getRequestPostParam('client');
            $services = $this->serviceRepository->getServicesByScheduleDates($date, $clientId);
        }else{
            return new JsonResponse(['services' => [], 'status' => false]);
        }

        return new JsonResponse(['services' => $services, 'status' => true]);
    }

    public function index(): Response
    {
        $services = $this->serviceRepository->findServices($this->filterService);
        $divisions = $this->divisionRepository->findAll();

        return $this->render('service/index.html.twig', [
            'totalResults' => $services['totalRegisters'],
            'lastPage' => $services['lastPage'],
            'currentPage' => $this->filterService->page,
            'services' => $services,
            'divisions' => $divisions,
            'filterService' => $this->filterService
        ]);
    }

    public function new(): RedirectResponse|Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            if($form->get('professionals')->getData() != null){
                /** @var User $user */
                foreach ($form->get('professionals')->getData() as $user){
                    $service->addProfessional($user);

                }
            };

            $this->serviceRepository->persist($service);

            return $this->redirectToRoute('service_index');
        }

        return $this->render('service/new.html.twig', [
            'service' => $service,
            'divisions' => $this->divisionRepository->findAll(),
            'users' => $this->userRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    public function show(string $service): Response
    {
        $service = $this->getEntity($service);

        $this->filterService->addFilter('service', [$service->getId()]);
        $users = $this->userRepository->findUsers($this->filterService);
        return $this->render('service/show.html.twig', [
            'service' => $service,
            'totalResults' => $users['totalRegisters'],
            'lastPage' => $users['lastPage'],
            'currentPage' => $this->filterService->page,
            'roles' => $this->roleRepository->findAll(),
            'users' => $users,
            'filterService' => $this->filterService
        ]);
    }

    public function edit(string $service): Response
    {
        $service = $this->getEntity($service);

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('professionals')->getData() != null){
                $this->serviceRepository->removeProfessionals($service);
                /** @var User $user */
                foreach ($form->get('professionals')->getData() as $user){
                    $service->addProfessional($user);

                }
            }

            $this->serviceRepository->persist($service);

            return $this->redirectToRoute('service_index');
        }

        return $this->render('service/edit.html.twig', [
            'service' => $service,
            'divisions' => $this->divisionRepository->findAll(),
            'users' => $this->userRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    public function delete(string $service): Response
    {
        $service = $this->getEntity($service);

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $this->getRequestPostParam('_token'))) {

            if(count($service->getAppointments()) > 0){
                $this->serviceRepository->setActive($service, false);
            }else{
                $this->serviceRepository->remove($service);
            }
        }

        return $this->redirectToRoute('service_index');
    }

    public function sumMinutes(string $servicesIds): int
    {
        $services = $this->serviceRepository->findServicesByIds($servicesIds);

        $total = 0;

        /** @var Service $service */
        foreach ($services as $service)
        {
            $total += intval($service->getNeededTime());
        }

        return $total;
    }

    public function getByClient(): JsonResponse
    {

        $finalServices = [];
        if(@$this->getRequestPostParam('client')){
            $finalServices = $this->serviceRepository->getServicesByClient($this->getRequestPostParam('client'));
        }

        $response = new JsonResponse($finalServices);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}