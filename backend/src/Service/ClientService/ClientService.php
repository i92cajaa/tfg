<?php


namespace App\Service\ClientService;


use App\Entity\Center\Center;
use App\Entity\Client\Client;
use App\Entity\Client\ClientHasDocument;
use App\Entity\Document\Document;
use App\Entity\Payment\PaymentMethod;
use App\Entity\Role\Role;
use App\Entity\Service\Division;
use App\Entity\Service\Service;
use App\Entity\User\User;
use App\Entity\User\UserHasClient;
use App\Form\ClientType;
use App\Repository\ClientHasDocumentRepository;
use App\Repository\RoleRepository;
use App\Repository\AppointmentRepository;
use App\Repository\CenterRepository;
use App\Repository\ClientRepository;
use App\Repository\DivisionRepository;
use App\Repository\DocumentRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\SchedulesRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserHasClientRepository;
use App\Repository\UserRepository;
use App\Service\DocumentService\DocumentService;
use App\Service\MailService;
use App\Service\TemplateService\TemplateTypeService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Utils\Util;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
use App\Service\UserService\UserService;

class ClientService extends AbstractService
{


    /**
     * @var ClientRepository
     */
    private ClientRepository $clientRepository;

    /**
     * @var CenterRepository
     */
    private CenterRepository $centerRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    private DocumentRepository $documentRepository;

    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;

    private ClientHasDocumentRepository $clientHasDocumentRepository;


    public function __construct(
        private readonly DocumentService $documentService,
        private readonly UserService $userService,
        private readonly MailService   $mailService,

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

        $this->roleRepository = $em->getRepository(Role::class);
        $this->appointmentRepository = $em->getRepository(Appointment::class);
        $this->serviceRepository = $em->getRepository(Service::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->divisionRepository = $em->getRepository(Division::class);
        $this->clientRepository = $em->getRepository(Client::class);
        $this->paymentRepository = $em->getRepository(PaymentMethod::class);
        $this->centerRepository = $em->getRepository(Center::class);
        $this->documentRepository = $em->getRepository(Document::class);
        $this->clientHasDocumentRepository = $em->getRepository(ClientHasDocument::class);
        $this->userHasClientRepository = $em->getRepository(UserHasClient::class);


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

    public function checkExist($fileName): bool
    {
        $clients = $this->clientRepository->findBy(['img_profile' => $fileName]);

        if(sizeof($clients) > 0){
            return false;
        }else{
            return true;
        }
    }

    public function findBy(array $criteria): array
    {
        return $this->clientRepository->findBy($criteria);
    }

    public function find(string $id): ?Client
    {
        return $this->clientRepository->find($id);
    }

    public function checkIfClientExist($email): ?Client
    {
        return $this->clientRepository->findOneByEmail($email);
    }

    public function createClient($data): ?Client
    {
        $client = new Client();

        if(array_key_exists('name',$data)){
            $client->setName($data['name']);
        }else{
            return null;
        }

        if(array_key_exists('surnames',$data)){
            $client->setSurnames($data['surnames']);
        }else{
            return null;
        }

        if(array_key_exists('phone',$data)){
            $client->setPhone1($data['phone']);
        }
        if(array_key_exists('email',$data)){
            $client->setEmail($data['email']);
        }else{
            return null;
        }

        if(array_key_exists('status',$data)){
            $client->setStatus($data['status']);
        }else{
            $client->setStatus(true);
        }

        if(array_key_exists('payment_method',$data)){
            $client->setPaymentPreference($this->paymentRepository->find($data['payment_method']));
        }else{
            $client->setPaymentPreference($this->paymentRepository->find(1));
        }

        if(array_key_exists('address',$data)){
            $client->setAddress($data['address']);
        }else{
            $client->setAddress(null);
        }

        if(array_key_exists('locale',$data)){
            $client->setLocale($data['locale']);
        }

        if(array_key_exists('timezone',$data)){
            $client->setTimezone($data['timezone']);
        }

        if(array_key_exists('availableTimeSlots',$data)){
            $client->setAvailableTimeSlotsRaw($data['availableTimeSlots']);
        }
        $password = Util::secure_random_string(10);
        $client->setCreatedAt(UTCDateTime::create('NOW'));
        $this->clientRepository->upgradePassword($client, $password);
        $this->clientRepository->persist($client);
        $this->email($client,$password);

        return $client;
    }


    public function index(): Response
    {
        $clients = [];
        foreach ($this->getUser()->getRoleIds() as $roleId) {
            if ($roleId == Role::ROLE_ADMIN){
                $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
                $clients = $this->clientRepository->findClients($this->filterService,$this->getUser()->getId(),$this->getUser()->isAdmin());
            }else{
                $clients = $this->clientRepository->findClients($this->filterService,$this->getUser()->getId(),$this->getUser()->isAdmin());
            }
        }
        //$clients = $this->clientRepository->findClients($this->filterService,$this->getUser()->getId(),$this->getUser()->isAdmin());

        return $this->render('client/index.html.twig', [
            'totalResults' => $clients['totalRegisters'],
            'lastPage' => $clients['lastPage'],
            'currentPage' => $this->filterService->page,
            'clients' => $clients['data'],
            'centers' => $this->centerRepository->findAll(),
            'filterService' => $this->filterService
        ]);
    }

    

    

    public function new(): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client, [
            'edit' => false,
        ]);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('password')->getData())$this->clientRepository->upgradePassword($client, $form->get('password')->getData());

            $client->setAlumni($form->get('alumni')->getData());
            $client->setNewCompany($form->get('newCompany')->getData());
            $client->setDigitalStartup($form->get('digitalStartup')->getData());

            $file = $form->get('logo')->getData();
            if($file != null){
                $imgProfile = $this->documentService->uploadDocument($file, 'clients');
                $client->setLogo($imgProfile);
            }
            $this->clientRepository->persist($client);
            $this->userService->newProject($client);
            return $this->redirectToRoute('client_index');
        }

        return $this->render('client/new.html.twig', [
            'client' => $client,
            'paymentMethods' => $this->paymentRepository->findBy(['active' => true]),
            'users' => $this->userRepository->findBy(['status' => true]),
            'form' => $form->createView(),
            'centers' => $this->centerRepository->findAll()
        ]);
    }


    public function email(Client $request,$password){
        $title = $this->translate('Dear')." ".$request->getName()." ".$this->translate('these are your login credentials', $request->getLocale());
        $content = "<p>" .$this->translate('Username' , $request->getLocale()).": ".$request->getEmail() . "</p>";
        $content .= "<p>" .$this->translate('Password', $request->getLocale()).": ".$password. "</p>";
        $content .= "<a href='https://fluxua.studio128k.com/clientUser/login'>" .$this->translate('Access platform', $request->getLocale()). "</a>";
        $this->mailService->sendEmailClient($request,null,$title,$content);
    }

    public function show(string $client): Response
    {
        $client = $this->clientRepository->find($client);
        $user = $this->userRepository->findOneBy(['email'=>$client->getEmail()]);
        $this->filterService->setLimit(10);
        $this->filterService->addFilter('client', [$client->getId()]);
        $appointments = $this->appointmentRepository->findAppointments($this->filterService);
        $documents = $client->getDocuments($user);
        foreach ($this->clientHasDocumentRepository->findBy(['client' => $client]) as $clientHasDocument) {
            $documents[] = $clientHasDocument->getDocument();
        }
        return $this->render('client/show.html.twig', [
            'payment_methods' => $this->paymentRepository->findAll(),
            'client' => $client,
            'clientId' => $client->getId(),
            'appointments' => $appointments,
            'users' => $this->userRepository->findNonAdminUsers(),
            'templateTypes' => $this->templateTypeService->findBy(['entity' => Appointment::ENTITY, 'active' => true]),
            'filterService' => $this->filterService,
            'divisions' => $this->divisionRepository->findAll(),
            'services' => $this->serviceRepository->findBy(['active' => true]),
            'documents' => $documents
        ]);
    }

    public function edit(string $clientId): Response
    {
        $client = $this->clientRepository->find($clientId);
        $user = $this->userRepository->findOneBy(['email'=>$client->getEmail()]);
        $form = $this->createForm(ClientType::class, $client, [
            'edit' => true,
        ]);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $client->setUpdatedAt(UTCDateTime::create('NOW'));

            $client->setAlumni($form->get('alumni')->getData());
            $client->setNewCompany($form->get('newCompany')->getData());
            $client->setDigitalStartup($form->get('digitalStartup')->getData());

            $usersWithClient = $this->userHasClientRepository->findBy(['client' => $client]);
            $user = null;

            foreach ($usersWithClient as $userClient) {
                if ($userClient->getUser()->isProject()) {
                    $user = $userClient->getUser();
                    break;
                }
            }

            if($form->get('password')->getData()) {
                $this->clientRepository->upgradePassword($client, $form->get('password')->getData());
                $this->userRepository->upgradePassword($user, $form->get('password')->getData());
            }

            $file = $form->get('logo')->getData();
            $newUser = $this->userRepository->findOneBy(['email'=>$client->getEmail()]);
            if($newUser == null){
                $user->setEmail($client->getEmail());
                $this->userRepository->persist($user);
            }

            if($file != null){
                $imgProfile = $this->documentService->uploadDocument($file, 'clients');
                $client->setLogo($imgProfile);
                try{
                    $user = $this->userRepository->findOneBy(['email'=>$client->getEmail()]);
                    $user->setImgProfile($imgProfile);
                    $this->userRepository->persist($user);
                }catch(Exception $e){
                }
            }




            $this->clientRepository->persist($client);

            return $this->redirectToRoute('client_index');
        }
        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'paymentMethods' => $this->paymentRepository->findBy(['active' => true]),
            'users' => $this->userRepository->findBy(['status' => true]),
            'form' => $form->createView(),
            'centers' => $this->centerRepository->findAll(),
            'documents' => $client->getDocuments($user)
        ]);
    }

    public function changeStatus(string $client): Response{
        $client = $this->clientRepository->find($client);

        if ($client->getAlumni()){
            $client->setAlumni(false);
        }else{
            $client->setAlumni(true);
        }
        $this->clientRepository->persist($client);
        return $this->redirectToRoute('client_index');
    }

    public function delete(string $client, Request $request): Response
    {

        if ($this->isCsrfTokenValid('delete'.$client, $this->getRequestPostParam('_token'))) {
            $client = $this->getEntity($client);
            if(sizeof($client->getAppointments()->toArray()) == 0 && sizeof($client->getTemplates()->toArray()) == 0){
                $this->clientRepository->remove($client);
                $request->getSession()->getFlashBag()->add('success', 'Proyecto borrado correctamente.');
            }else{
                $client->setStatus(false);
                $this->clientRepository->persist($client);
                $request->getSession()->getFlashBag()->add('success', 'Se ha desactivado el proyecto, ya que tiene mentorias asignadas');

            }

        }

        return $this->redirectToRoute('client_index');
    }

    public function uploadClientDocument($clientId ,Request $request){
        $client = $this->clientRepository->find($clientId);

        $document = $this->documentService->uploadRequest($request->files->get('document')->getClientOriginalName());

        $documentType = $request->request->get('uploadDocument');

        if($documentType != "" || $documentType != null){
            switch ($documentType){
                case 'setDocumentAdhesion':
                    $this->setDocumentAdhesion($client, $document);
                    break;
                case 'setDocumentConfidencial':
                    $this->setDocumentConfidencial($client, $document);
                    break;
                case 'addDocument':
                    $client->getUser()->addDocument($document);
                    break;
            }
            $this->clientRepository->persist($client);
        }

        return $this->redirectToRoute('client_show',['client'=> $clientId]);
    }


    public function setDocumentAdhesion(Client $client, ?Document $document_adhesion)
    {
        $user = $this->userRepository->find($client->getUser());
        $old_document = $user->getDocumentAdhesion();
        $user->setDocumentAdhesion($document_adhesion);
        if ($old_document)$this->documentRepository->deleteDocument($old_document);
    }
    public function setDocumentConfidencial(Client $client, ?Document $document_adhesion)
    {
        $user = $this->userRepository->find($client->getUser());
        $old_document = $user->getDocumentConfidencial();
        $user->setDocumentConfidencial($document_adhesion);
        if ($old_document)$this->documentRepository->deleteDocument($old_document);
    }

    public function changeStatusOrAlumni(): Response{
        $client = $this->clientRepository->find($this->getRequestPostParam('clientId'));

        $status = $this->getRequestPostParam('active');

        if ($status == '1') {
            if ($client->getAlumni()){
                $client->setAlumni(false);
            }
            $client->setStatus(true);
        } elseif ($status == '2') {
            if (!$client->isStatus()) {
                $client->setStatus(true);
            }
            $client->setAlumni(true);
        } else {
            $client->setStatus(false);
            $client->setAlumni(false);
        }

        $this->clientRepository->persist($client);

        return new JsonResponse(['data' => $status,'success' => true, 'message' => 'done'], 200);
    }

    public function rememberPassword(): Response
    {

        return $this->render('security/rememberPassword.html.twig',);
    }
}