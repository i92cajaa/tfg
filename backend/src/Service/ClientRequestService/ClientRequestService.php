<?php


namespace App\Service\ClientRequestService;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\ClientRequest\ClientRequest;
use App\Entity\Status\Status;
use App\Entity\User\User;
use App\Form\UserPasswordUpdateType;
use App\Form\ClientRequestType;
use App\Repository\ClientRequestRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\ClientService\ClientService;
use App\Service\DocumentService\DocumentService;
use App\Service\MailService;
use App\Service\StripeService\StripeService;
use App\Service\TemplateService\TemplateTypeService;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class ClientRequestService extends AbstractService
{


    /**
     * @var ClientRequestRepository
     */
    private ClientRequestRepository $clientRequestRepository;

    /**
     * @var StatusRepository
     */
    private StatusRepository $statusRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(
        private readonly TemplateTypeService $templateTypeService,
        private readonly DocumentService $documentService,
        private readonly ClientService $clientService,
        private readonly StripeService $stripeService,
        private readonly MailService   $mailService,

        EntityManagerInterface $em,
        RouterInterface       $router,
        Environment           $twig,
        RequestStack          $clientRequestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface      $tokenManager,
        FormFactoryInterface           $formFactory,
        SerializerInterface            $serializer,
        TranslatorInterface $translator
    )
    {
        $this->clientRequestRepository = $em->getRepository(ClientRequest::class);
        $this->statusRepository = $em->getRepository(Status::class);
        $this->userRepository = $em->getRepository(User::class);

        parent::__construct(
            $clientRequestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->clientRequestRepository
        );
    }

    public function findBy(array $criteria): array
    {
        return $this->clientRequestRepository->findBy($criteria);
    }

    public function find(string $id): ?ClientRequest
    {
        return $this->clientRequestRepository->find($id);
    }

    public function index(): Response
    {
        $clientRequests = $this->clientRequestRepository->findClientRequests($this->filterService);

        return $this->render('client_request/index.html.twig', [
            'totalResults' => $clientRequests['totalRegisters'],
            'lastPage' => $clientRequests['lastPage'],
            'currentPage' => $this->filterService->page,
            'clientRequests' => $clientRequests['data'],
            'filterService' => $this->filterService,
            'statuses' => $this->statusRepository->findBy(['entityType' => ClientRequest::ENTITY]),
            'users' => $this->userRepository->findAll()
        ]);
    }

    public function new(): Response
    {
        $clientRequest = new ClientRequest();
        $form = $this->createForm(ClientRequestType::class, $clientRequest);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $clientRequest->setStatus($this->statusRepository->find(Status::STATUS_REQUEST_PENDING));

            if($form->getExtraData() != null) {
                $clientRequest->setAvailableTimeSlots(@$form->getExtraData()['availableTimeSlots']?:[]);
            }

            $amount = 1900;
            $currency = "eur";
            $productName = "Cuestionario consulta inicial.";

            $stripeData = $this->stripeService->checkoutSession($amount,$currency,$productName);

            $clientRequest->setStripeId($stripeData['id']);
            $clientRequest->setPaid(false);
            $this->clientRequestRepository->persist($clientRequest);
            return $this->redirect($stripeData['url']);
        }else{
            foreach ($form->getErrors() as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('client_request/new.html.twig', [
            'clientRequest' => $clientRequest,
            'form' => $form->createView(),
        ]);
    }




    public function data(): Response {
        return $this->render('client_request/credentials.html.twig');
    }

    public function legalSign(): Response {
        return $this->render('client_request/legal_sign.html.twig');
    }

    public function success(): Response {
        return $this->render('client_request/success.html.twig');
    }

    public function create(): Response {
        return $this->render('client_request/new.html.twig');
    }


    public function step1(string $clientRequest): Response
    {
        $clientRequest = $this->getEntity($clientRequest);

        return $this->render('client_request/step1.html.twig', [
            'clientRequest' => $clientRequest,
            'statuses' => $this->statusRepository->findBy(['entityType' => ClientRequest::ENTITY]),
        ]);
    }

    public function show(string $clientRequest): Response
    {
        $clientRequest = $this->getEntity($clientRequest);

        return $this->render('client_request/show.html.twig', [
            'clientRequest' => $clientRequest,
            'statuses' => $this->statusRepository->findBy(['entityType' => ClientRequest::ENTITY]),
        ]);
    }

    public function edit(string $clientRequest): Response
    {
        $clientRequest = $this->getEntity($clientRequest);

        $form = $this->createForm(ClientRequestType::class, $clientRequest);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            if($form->getExtraData() != null) {
                $clientRequest->setAvailableTimeSlots(@$form->getExtraData()['availableTimeSlots']?:[]);
            }

            $this->clientRequestRepository->persist($clientRequest);

            return $this->redirectBack();
        }

        return $this->render('client_request/edit.html.twig', [
            'clientRequest' => $clientRequest,
            'form' => $form->createView(),
        ]);
    }

    public function rememberPassword(): Response
    {

        return $this->render('security/rememberPassword.html.twig',);
    }



    public function editStatus(string $clientRequest): Response
    {
        $clientRequest = $this->getEntity($clientRequest);

        if ($this->isCsrfTokenValid('change-status-client-request', $this->getRequestPostParam('_token'))) {

            $status = $this->statusRepository->find($this->getRequestPostParam('status'));
            $clientRequest->setStatus($status);

            $this->clientRequestRepository->persist($clientRequest);

            return $this->redirectToRoute('client_request_index');
        }

        return $this->redirectBack();
    }

    public function validate(string $clientRequestId): Response
    {
        /** @var ClientRequest $clientRequest */
        $clientRequest = $this->getEntity($clientRequestId);

        if ($this->isCsrfTokenValid('validate-client-request', $this->getRequestPostParam('_token'))) {

            $client = $this->clientService->createClient(
                $clientRequest->formatClientData()
            );

            $user = $this->userRepository->find($this->getRequestPostParam('user'));

            if($client && $user){
                $client->addUser($user);

                $status = $this->statusRepository->find(Status::STATUS_REQUEST_COMPLETED);
                $clientRequest->setStatus($status);
            }


            $this->clientRequestRepository->persist($clientRequest);

            return $this->redirectToRoute('client_request_index');
        }

        return $this->redirectBack();
    }

    public function delete(string $clientRequest): Response
    {
        $clientRequest = $this->getEntity($clientRequest);

        if ($this->isCsrfTokenValid('delete'.$clientRequest->getId(), $this->getRequestPostParam('_token'))) {
            $this->clientRequestRepository->remove($clientRequest);
        }

        return $this->redirectToRoute('client_request_index');
    }

}