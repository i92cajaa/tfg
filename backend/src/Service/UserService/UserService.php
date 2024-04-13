<?php

namespace App\Service\UserService;

use App\Entity\Client\ClientHasDocument;
use App\Repository\ClientHasDocumentRepository;
use App\Repository\SurveyRangeRepository;
use App\Service\FilterService;
use DateTime;
use App\Entity\Area\Area;
use App\Entity\Center\Center;
use App\Entity\Client\Client;
use App\Entity\Document\Document;
use App\Entity\Role\Role;
use App\Entity\Role\RoleHasPermission;
use App\Entity\Status\Status;
use App\Entity\User\User;
use App\Entity\User\UserHasPermission;
use App\Form\UserPasswordUpdateType;
use App\Form\UserType;
use App\Repository\AppointmentRepository;
use App\Repository\CenterRepository;
use App\Repository\ClientRepository;
use App\Repository\DivisionRepository;
use App\Repository\DocumentRepository;
use App\Repository\RoleRepository;
use App\Repository\SchedulesRepository;
use App\Repository\ServiceRepository;
use App\Repository\StatusRepository;
use App\Repository\AreaRepository;
use App\Service\DocumentService\DocumentService;
use App\Service\PermissionService\PermissionService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use Twig\Environment;
use ZipArchive;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class UserService extends AbstractService
{

    const UPLOAD_FILES_PATH = 'images/users';

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;

    /**
     * @var AreaRepository
     */
    private AreaRepository $areaRepository;
    /**
     * @var CenterRepository
     */
    private CenterRepository $centerRepository;

    /**
     * @var ClientRepository
     */
    private ClientRepository $clientRepository;

    /**
     * @var StatusRepository
     */
    private StatusRepository $statusRepository;

    /**
     * @var EntityRepository|DocumentRepository
     */
    private DocumentRepository|EntityRepository $documentRepository;

    /**
     * @var ClientHasDocumentRepository|EntityRepository
     */
    private ClientHasDocumentRepository|EntityRepository $clientHasDocumentRepository;

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly PermissionService $permissionService,

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
    ) {
        $this->userRepository = $em->getRepository(User::class);
        $this->roleRepository = $em->getRepository(Role::class);
        $this->areaRepository = $em->getRepository(Area::class);
        $this->centerRepository = $em->getRepository(Center::class);
        $this->clientRepository = $em->getRepository(Client::class);
        $this->statusRepository = $em->getRepository(Status::class);
        $this->documentRepository = $em->getRepository(Document::class);
        $this->clientHasDocumentRepository = $em->getRepository(ClientHasDocument::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->userRepository
        );
    }

    public function getAll(): array
    {
        return $this->userRepository->findAll();
    }

    public function findUsersByServiceIds(array $services): array
    {
        return $this->userRepository->findUsersByServices($services);
    }

    public function index(): Response
    {
        $users = $this->userRepository->findUsers($this->filterService);
        $roles = $this->roleRepository->findAll();

        //dd($users);

        return $this->render('user/index.html.twig', [
            'totalResults' => $users['totalRegisters'],
            'lastPage' => $users['lastPage'],
            'currentPage' => $this->filterService->page,
            'users' => $users['users'],
            'centers' => $this->centerRepository->findAll(),
            'filterService' => $this->filterService,
            'roles' => $roles
        ]);
    }

    public function mentoresIndex(): Response
    {
        $this->filterService->addFilter('roles', [0 => 3]);
        $this->filterService->addFilter('status', 1);
        $users = $this->userRepository->findUsers($this->filterService);
        $roles = $this->roleRepository->findAll();

        $allMentors = $this->userRepository->findUsers($this->filterService, true);

        $selectedSurveyRange = $this->surveyRangeRepository->findOneBy(['status' => true]);
        return $this->render('user/index_mentor.html.twig', [
            'totalResults' => $users['totalRegisters'],
            'lastPage' => $users['lastPage'],
            'currentPage' => $this->filterService->page,
            'users' => $users['users'],
            'centers' => $this->centerRepository->findAll(),
            'filterService' => $this->filterService,
            'roles' => $roles,
            'surveyRanges' => $this->surveyRangeRepository->findAll(),
            'selectedSurveyRange' => $selectedSurveyRange,
            'mentors' => $allMentors['data']
        ]);
    }

    public function dashboard(?string $nameSearch, ?string $center, ?DateTime $startDate, ?DateTime $endDate,?DateTime $startDateYear): Response
    {
        $this->filterService->addFilter('roles', [0 => 3]);
        $this->filterService->addFilter('status', 1);

        if ($nameSearch !== null) {
            $this->filterService->addFilter('info', $nameSearch);
        }

        $users = $this->userRepository->findUsers($this->filterService, false);
        $roles = $this->roleRepository->findAll();
        $clients = [];

        $this->filterService->addFilter('info', '');

        if ($center !== null) {
            $this->filterService->addFilter('center', $center);
        }

        $clients = $this->clientRepository->findClients($this->filterService, $this->getUser()->getId(), $this->getUser()->isAdmin());

        $appointments = $this->serviceRepository->getServiceAppointmentsCount($startDate, $endDate);
        $appointmentsYear =$this->serviceRepository->getMentorshipCountByMonth($startDateYear);
    

        return $this->render('dashboard/dashboard.html.twig', [
            'appointments' => $appointments,
            'appointmentsYear' => $appointmentsYear,
            'clients' => $clients['data'],
            'centers' => $this->centerRepository->findAll(),
            'filterService' => $this->filterService,
            'totalResultsMentor' => $users['totalRegisters'],
            'lastPageMentor' => $users['lastPage'],
            'currentPage' => $this->filterService->page,
            'users' => $users['users'],
            'roles' => $roles,
        ]);
    }


    public function new()
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($this->getCurrentRequest());
        $permissions = $this->permissionService->getAvailablePermissions();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // check if user exists
                $userExists = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
                if ($userExists) {
                    $this->addFlash("error", $this->translate('User with this email already exists'));
                    return $this->render('user/new.html.twig', [
                        'user' => $user,
                        'form' => $form->createView(),
                        'roles' => $this->roleRepository->findAll(),
                        'permissions' => $permissions,
                        'edit' => false
                    ]);
                }
                if ($form->get('roles')->getData() != null) {
                    $rol = $form->get('roles')->getData();
                    $user->addRole($form->get('roles')->getData());
                    foreach ($rol->getPermissions() as $roleHasPermission) {
                        $userHasPermission = (new UserHasPermission())->setUser($user)->setPermission($roleHasPermission->getPermission());
                        $user->addPermission($userHasPermission);
                    }
                };
                $center = $form->get('center')->getData();
                if ($center != null) {
                    $user->setCenter($center);
                }
                $areas = $form->get('areas')->getData();
                if ($areas != null) {
                    foreach ($form->get('areas')->getData() as $area) {
                        $user->addArea($area);
                    }
                }
                if ($form->get('password')->getData() != $user->getPassword() && $form->get('password')->getData() != null && $form->get('password')->getData() != "") {
                    $this->userRepository->upgradePassword($user, $form->get('password')->getData());
                }
                $file = $form->get('img_profile')->getData();
                $this->userRepository->persist($user);
                if ($file != null) {
                    $imgProfile = $this->documentService->uploadDocument($file, self::UPLOAD_FILES_PATH);
                    $user->setImgProfile($imgProfile);
                    $this->userRepository->persist($user);
                }
                $this->schedulesRepository->createAllWeekSchedules($user);


                $this->getSession()->getFlashBag()->add('success', 'Usuario creado correctamente.');
                return $this->redirectToRoute('user_index');
            } catch (\Exception $error) {
                $this->getSession()->getFlashBag()->add('danger', 'Error al crear nuevo usuario.');
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'areas' => $this->areaRepository->findAll(),
            'centers' => $this->centerRepository->findAll(),
            'roles' => $this->roleRepository->findAll(),
            'permissions' => $permissions,
            'edit' => false
        ]);
    }

    public function mentoresByArea(): JsonResponse
    {
        $mentores = [];
        if (@$this->getRequestParam('area')) {
            $mentores = $this->userRepository->findMentorUsersByArea(@$this->getRequestParam('area'));
        }
        $mentores = $this->uniqueMentor($mentores);
        $response = new JsonResponse(['mentores' => $mentores]);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function allmentores(): JsonResponse
    {
        $mentores = $this->userRepository->findMentorUsers();
        $mentores = $this->uniqueMentor($mentores);

        $response = new JsonResponse(['mentores' => $mentores]);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function uniqueMentor(array $array): array
    {
        $finalArray = [];
        foreach ($array as $area) {

            $areaArray = [
                'id' => $area->getId(),
                'name' => $area->getFullNameMentor()
            ];

            $finalArray[] = $areaArray;
        }
        return $finalArray;
    }


    public function newProject(Client $client, ?array $documents = null, ?UploadedFile $documentConfidencial = null, ?UploadedFile $documentAdhesion = null)
    {
        try {
            $user = new User();
            $user->setEmail($client->getEmail());
            $parts = explode(" ", $client->getRepresentative());
            $name = $parts[0];
            $surnames = implode(" ", array_slice($parts, 1));
            $user->setName($name);
            $user->setSurnames($surnames);
            $user->setPhone($client->getPhone());
            $user->setCenter($client->getCenter());
            $user->setPassword($client->getPassword());
            $user->setImgProfile($client->getLogo());
            if ($documents != null) {
                foreach ($documents as $document) {
                    if ($document != null) {
                        $document = $this->documentService->uploadDocument($document, 'clients');
                        $user->addDocument($document);
                    }
                }
            }
            if ($documentConfidencial != null) {
                $document = $this->documentService->uploadDocument($documentConfidencial, 'clients');
                $user->setDocumentConfidencial($document);
            }
            if ($documentAdhesion != null) {
                $document = $this->documentService->uploadDocument($documentAdhesion, 'clients');
                $user->setDocumentAdhesion($document);
            }

            // check if user exists
            $userExists = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($userExists) {
                $this->addFlash("error", $this->translate('User with this email already exists'));
                return false;
            }

            $projectRole = $this->roleRepository->find(4);
            $user->addRole($projectRole);
            /** @var RoleHasPermission $roleHasPermission */
            foreach ($projectRole->getPermissions() as $roleHasPermission) {
                $userHasPermission = (new UserHasPermission())->setUser($user)->setPermission($roleHasPermission->getPermission());
                $user->addPermission($userHasPermission);
            }
            $user->addClient($client);

            $this->userRepository->persist($user);
            $this->schedulesRepository->createAllWeekSchedules($user);
            $this->getSession()->getFlashBag()->add('success', 'Usuario creado correctamente.');
            return true;
        } catch (\Exception $error) {
            $this->getSession()->getFlashBag()->add('danger', 'Error al crear usuario.');
        }
    }


    public function show(string $userId): Response
    {
        $user = $this->getEntity($userId);
        $services = $this->serviceRepository->findAll();
        $this->filterService->addFilter('user', $user->getId());
//        if($user->isProject()){
//            $this->filterService->addFilter('client', $user->getClient());
//        }
        $this->filterService->addFilter('services', $this->serviceRepository->findBy(['forAdmin' => false]));
        $appointments = $this->appointmentRepository->findAppointments($this->filterService);
        $schedules = $this->schedulesRepository->findBy(['user' => $user, 'status' => 1], ['timeFrom' => 'ASC']);

        $selectedSurveyRange = $this->surveyRangeRepository->findOneBy(['status' => true]);
        return $this->render('user/show.html.twig', [
            'totalResults' => $appointments['totalRegisters'],
            'lastPage' => $appointments['lastPage'],
            'totalAmount' => $appointments['totalAmount'],
            'currentPage' => $this->filterService->page,
            'user' => $user,
            'appointments' => $appointments,
            'schedules' => $schedules,
            'services' => $services,
            'divisions' => $this->divisionRepository->findAll(),
            'allServices' => $this->serviceRepository->findAll(),
            'clients' => $this->clientRepository->findAll(),
            'filterService' => $this->filterService,
            'status' => $this->statusRepository->findAll(),
            'permissions' => $this->permissionService->getAvailablePermissions(),
            'surveyRanges' => $this->surveyRangeRepository->findAll(),
            'selectedSurveyRange' => $selectedSurveyRange
        ]);
    }


    public function changePassword(string $token, Request $request): Response
    {
        $isPasswordValid = true;
        $isRepeatPasswordValid = true;
        $form = $this->createForm(UserPasswordUpdateType::class, null);
        $form->handleRequest($this->getCurrentRequest());
        $formView = $form->createView();
        $user = $this->userRepository->findUserByToken($token);
        if (!$user) {

            return $this->render('user/changePasswordScreen.html.twig', [
                'token' => $token,
                'isPasswordValid' => $isPasswordValid,
                'isRepeatPasswordValid' => $isRepeatPasswordValid,
                'form' => $formView,
                'user' => $user
            ]);
        }
        $idUser = $user->getId();




        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('password')->getData() != null && $form->get('password')->getData() != "") {
                $this->userRepository->upgradePassword($user, $form->get('password')->getData());
                $this->userRepository->updateUserTokenByIdNull($idUser);
                $this->addFlash('success', $this->translate('Contraseña cambiada con éxito'));
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('user/changePasswordScreen.html.twig', [
            'token' => $token,
            'isPasswordValid' => $isPasswordValid,
            'isRepeatPasswordValid' => $isRepeatPasswordValid,
            'form' => $formView,
            'user' => $user
        ]);
    }

    public function user_view_profile(string $userId, Request $request): Response
    {
        $user = $this->getEntity($userId);
        $form = $this->createForm(UserPasswordUpdateType::class, null);
        $form->handleRequest($this->getCurrentRequest());
        $formView = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('password')->getData() != null && $form->get('password')->getData() != "") {
                $this->userRepository->upgradePassword($user, $form->get('password')->getData());
                $this->getSession()->getFlashBag()->add('success', 'Contraseña actualizada.');
                return $this->redirect($request->getUri());
            }
        }

        $services = $this->serviceRepository->findAll();
        $this->filterService->addFilter('user', $user->getId());
        $this->filterService->addFilter('services', $this->serviceRepository->findBy(['forAdmin' => false]));
        $appointments = $this->appointmentRepository->findAppointments($this->filterService);
        $schedules = $this->schedulesRepository->findBy(['user' => $user, 'status' => 1], ['timeFrom' => 'ASC']);
//        if($this->getUser()->isProject()){
//            $surveys = $this->templateTypeService->findBy(['entity' => Client::ENTITY, 'active' => true]);
//        }else{
//            $surveys = $this->templateTypeService->findBy(['entity' => User::ENTITY, 'active' => true]);
//        }

        $surveys = $this->clientHasDocumentRepository->findBy(['client' => $user->getClient()]);

        return $this->render('user/show_profile.html.twig', [
            'totalResults' => $appointments['totalRegisters'],
            'lastPage' => $appointments['lastPage'],
            'totalAmount' => $appointments['totalAmount'],
            'currentPage' => $this->filterService->page,
            'surveys' => $surveys,
            'user' => $user,
            'appointments' => $appointments,
            'schedules' => $schedules,
            'services' => $services,
            'form' => $formView,
            'divisions' => $this->divisionRepository->findAll(),
            'allServices' => $this->serviceRepository->findAll(),
            'clients' => $this->clientRepository->findAll(),
            'filterService' => $this->filterService,
            'status' => $this->statusRepository->findAll(),
            'permissions' => $this->permissionService->getAvailablePermissions()
        ]);
    }

    public function edit(string $userId): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $this->getEntity($userId);

        if (!$user) {
            throw new \Exception("User not found");
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($this->getCurrentRequest());
        $permissions = $this->permissionService->getAvailablePermissions();

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(UTCDateTime::create('NOW'));

            // check if user exists
            $userExists = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($userExists and $userExists->getId() != $user->getId()) {
                $this->addFlash("error", $this->translate('User with this email already exists') . " " . $userExists->getEmail());
                return $this->redirectToRoute('user_edit', ['user' => $userId]);
            }

            if ($form->get('password')->getData() != $user->getPassword() && $form->get('password')->getData() != null && $form->get('password')->getData() != "") {
                $this->userRepository->upgradePassword($user, $form->get('password')->getData());
            }

            $center = $form->get('center')->getData();
            if ($center != null) {
                $user->setCenter($center);
            }

            $areas = $form->get('areas')->getData();

            if ($areas != null) {
                //borrar todas las areas para q puedas asignar la misma
                $this->userRepository->removeAllAreas($user);
                foreach ($areas as $area) {
                    $user->addArea($area);
                }
            }

            if ($form->get('roles')->getData() != null) {
                if ($form->get('roles')->getData()->getName() != $user->getRoles()[0]) {
                    $this->userRepository->removeAllRoles($user);
                    $user->addRole($form->get('roles')->getData());
                    $this->userRepository->removeAllPermissions($user);
                    foreach ($form->get('roles')->getData()->getPermissions() as $roleHasPermission) {
                        $user->addPermission((new UserHasPermission())->setUser($user)->setPermission($roleHasPermission->getPermission()));
                    }
                } else {
                    $permissionsArray = @$form->getExtraData()['permissions'] ?: [];
                    $permissions = $this->permissionService->generatePermissionsArray($user, $permissionsArray);
                    foreach ($permissions as $permission) {
                        $user->addPermission($permission);
                    }
                }
            }

            if ($form->getExtraData() != null) {
                if ($form->getExtraData()['delete_image'] == 'true') {
                    if ($user->getImgProfile() != null) {
                        $user->setImgProfile(null);
                    }
                }
            }

            $this->userRepository->persist($user);

            $file = $form->get('img_profile')->getData();
            if ($file != null) {
                $imgProfile = $this->documentService->uploadDocument($file, self::UPLOAD_FILES_PATH);
                $user->setImgProfile($imgProfile);
                $this->userRepository->persist($user);
            }

            $samePage = $form->get('same_page')->getData();
            if ($samePage === "true") {
                return $this->redirectToRoute('user_edit', ['user' => $user->getId()]);
            }

            return $this->redirectToRoute('user_show', ['user' => $user->getId()]);
        }


        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'areas' => $this->areaRepository->findAll(),
            'centers' => $this->centerRepository->findAll(),
            'roles' => $this->roleRepository->findAll(),
            'permissions' => $permissions,
            'edit' => true
        ]);
    }

    public function change_status(string $user): Response
    {
        $user = $this->getEntity($user);

        try {
            if ($user->getStatus()) {
                $this->userRepository->changeStatus($user, false);
                if ($user->isProject()){
                    $projects = $this->userHasClientRepository->findBy(['user'=>$user]);
                    foreach($projects as $project){
                        $project = $project->getClient();
                    }
                    $this->clientRepository->changeStatus($project, false);
                }
                $this->getSession()->getFlashBag()->add('success', 'Usuario desactivado correctamente.');
            } else {
                $this->userRepository->changeStatus($user, true);
                if ($user->isProject()){
                    $projects = $this->userHasClientRepository->findBy(['user'=>$user]);
                    foreach($projects as $project){
                        $project = $project->getClient();
                    }
                    $this->clientRepository->changeStatus($project, true);
                }
                $this->getSession()->getFlashBag()->add('success', 'Usuario activado correctamente.');
            }
            $this->userRepository->persist($user);
        } catch (\Exception $error) {
            $this->getSession()->getFlashBag()->add('danger', 'Error al cambiar el estado del usuario');
        }

        return $this->redirectToRoute('user_index');
    }

    public function delete(string $user): Response
    {
        $user = $this->getEntity($user);

        try {
            if (sizeof($user->getAppointments()->toArray()) == 0) {
                $this->userRepository->remove($user);
                $this->getSession()->getFlashBag()->add('success', 'Usuario borrado correctamente.');
            } else {
                $this->getSession()->getFlashBag()->add('success', 'Se ha desactivado el usuario, ya que tiene mentorias asignadas');
                $this->userRepository->changeStatus($user, false);
            }
        } catch (\Exception $error) {
            $this->getSession()->getFlashBag()->add('danger', 'Error al eliminar el usuario');
        }

        return $this->redirectToRoute('user_index');
    }

    public function getUsersByServiceAndDate(): Response
    {

        $users = $this->userRepository->findProfessionalByServicesAndDate($this->getRequestPostParam('services'), $this->getRequestPostParam('date'));

        $response = new JsonResponse(['users' => $users]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function getUsersByService()
    {
        $users = $this->userRepository->findProfessionalByServices($this->getRequestPostParam('services'));

        $response = new JsonResponse(['users' => $users]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }


    public function removeService(): Response
    {
        $user = $this->userRepository->find($this->getRequestPostParam('user_id'));

        if ($this->isCsrfTokenValid('edit', $this->getRequestPostParam('_token'))) {
            $service = $this->serviceRepository->find($this->getRequestPostParam('service_id'));
            $this->serviceRepository->remove($service);
        }

        return $this->redirectToRoute('user_show', ['user' => $user->getId()]);
    }

    public function addService()
    {
        if ($this->isCsrfTokenValid('edit', $this->getRequestPostParam('_token'))) {
            $user = $this->userRepository->find($this->getRequestPostParam('user_id'));
            if (is_array($this->getRequestPostParam('service_id')) && sizeof($this->getRequestPostParam('service_id')) > 0) {
                foreach ($this->getRequestPostParam('service_id') as $serviceId) {
                    $service = $this->serviceRepository->find($serviceId);

                    $user->addService($service);
                }
            } elseif (!is_array($this->getRequestPostParam('service_id')) && $this->getRequestPostParam('service_id') != null) {
                $service = $this->serviceRepository->find($this->getRequestPostParam('service_id'));

                $user->addService($service);
            };

            $this->userRepository->persist($user);

            return $this->redirectToRoute('user_show', ['user' => $user->getId()]);
        }
    }

    public function checkExist($fileName): bool
    {
        $users = $this->userRepository->findBy(['img_profile' => $fileName]);

        if (sizeof($users) > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function uploadUserDocument($userId, Request $request): RedirectResponse
    {
        $user = $this->userRepository->find($userId);

        $document = $this->documentService->uploadRequest($request->files->get('document')->getClientOriginalName());

        $documentType = $request->request->get('uploadDocument');

        if ($documentType != "" || $documentType != null) {
            switch ($documentType) {
                case 'setDocumentAdhesion':
                    $this->setDocumentAdhesion($user, $document);
                    break;
                case 'setDocumentConfidencial':
                    $this->setDocumentConfidencial($user, $document);
                    break;
                case 'setDocumentImage':
                    $this->setDocumentImage($user, $document);
                    break;
                case 'setDocumentDeontological':
                    $this->setDocumentDeontological($user, $document);
                    break;
                case 'setDocumentAnexo':
                    $this->setDocumentAnexo($user, $document);
                    break;
                case 'addDocument':
                    $user->addDocument($document);
                    break;
            }
            $this->userRepository->persist($user);
        }

        return $this->redirectBack();
    }

    public function setDocumentAdhesion(User $user, ?Document $document_adhesion)
    {
        $old_document = $user->getDocumentAdhesion();
        $user->setDocumentAdhesion($document_adhesion);
        if ($old_document) $this->documentRepository->deleteDocument($old_document);
    }
    public function setDocumentConfidencial(User $user, ?Document $document_adhesion)
    {
        $old_document = $user->getDocumentConfidencial();
        $user->setDocumentConfidencial($document_adhesion);
        if ($old_document) $this->documentRepository->deleteDocument($old_document);
    }

    public function setDocumentImage(User $user, ?Document $document_image)
    {
        $old_document = $user->getDocumentImage();
        $user->setDocumentImage($document_image);
        if ($old_document) $this->documentRepository->deleteDocument($old_document);
    }

    public function setDocumentDeontological(User $user, ?Document $document_deontological)
    {
        $old_document = $user->getDocumentDeontological();
        $user->setDocumentDeontological($document_deontological);
        if ($old_document) $this->documentRepository->deleteDocument($old_document);
    }

    public function setDocumentAnexo(User $user, ?Document $document_anexo)
    {
        $old_document = $user->getDocumentAnexo();
        $user->setDocumentAnexo($document_anexo);
        if ($old_document) $this->documentRepository->deleteDocument($old_document);
    }


    public function downloadMentorAssets(): Response
    {
        $mentorAssetsDirectory = $this->kernel->getProjectDir() . '/public/assets/mentors';
        $mentorAssetFilenames = [
            'AcuerdoConfidencialidad_V01.pdf',
            'AnexoIObligacionesTecnicas_V01.pdf',
            'CodigoDeontologico_V01.pdf',
            'DerechosImagen_V01.pdf',
        ];
        $zipFilename = 'modelosMentores.zip';
        $zip = new ZipArchive();
        $zip->open($zipFilename, ZipArchive::CREATE);
        foreach ($mentorAssetFilenames as $filename) {
            $filePath = $mentorAssetsDirectory . '/' . $filename;
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $filename);
            }
        }

        $zip->close();
        $response = new BinaryFileResponse($zipFilename);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $zipFilename
        );

        $response->headers->set('Content-Type', 'application/zip');

        register_shutdown_function(function () use ($zipFilename) {
            if (file_exists($zipFilename)) {
                unlink($zipFilename);
            }
        });

        return $response;
    }

    public function getUserById(string $id, ?bool $array = false)
    {
        return $this->userRepository->getUserById($id, $array);
    }

    public function allSurveys()
    {
        $clientId = $this->getRequestParam('client');

        if ($clientId == null) {
            $clientId = $this->getUser()->getClient()->getId();
        }

        $userHasClient = $this->userHasClientRepository->findBy(['client' => $clientId]);

        $mentors = [];
        foreach ($userHasClient as $mentor) {
            if (!$mentor->getUser()->isProject()) {
                $mentors[] = $mentor->getUser();
            }
        }

        $surveyRangeId = $this->getRequestPostParam('surveyRange');

        if ($surveyRangeId == null) {
            $surveyRange = $this->surveyRangeRepository->findOneBy(['status' => true]);
        } else {
            $surveyRange = $this->surveyRangeRepository->find($surveyRangeId);
        }

        if ($surveyRange != null) {
            if ($surveyRange->getStartDate() != null) $this->filterService->addFilter('date_from', $surveyRange->getStartDate()->format('d-m-Y'));
            if ($surveyRange->getEndDate() != null) $this->filterService->addFilter('date_to', $surveyRange->getEndDate()->format('d-m-Y'));
        }

        $this->filterService->addFilter('client', [$clientId]);
        $this->filterService->addFilter('services', $this->serviceRepository->findBy(['forAdmin'=>false, 'forClient' => false]));

        $this->filterService->addFilter('statusType', 9);

        $appointments = $this->appointmentRepository->findAppointments($this->filterService);

        $allSurveyRanges = $this->surveyRangeRepository->findAll();

        $mentorsFinished = [];
        foreach ($mentors as $mentor) {
            foreach ($appointments['data'] as $appointment) {
                if ($appointment->getUser()->getId() == $mentor->getId()) {
                    $mentorsFinished[] = $mentor;
                    break;
                }
            }
        }

        $mentorsAdminFinished = [];
        foreach ($mentorsFinished as $mentor) {
            // Verificar si los appointments del mentor están dentro del rango de fechas de cada surveyRange
            foreach ($allSurveyRanges as $surveyRangeCheck) {
                $mentorAppointments = []; // Almacenar los appointments del mentor actual
                foreach ($appointments['data'] as $appointment) {
                    if ($appointment->getUser()->getId() == $mentor->getId() &&
                        $appointment->getTimeTo() >= $surveyRangeCheck->getStartDate() &&
                        $appointment->getTimeTo() <= $surveyRangeCheck->getEndDate()) {
                        $mentorAppointments[] = $appointment;
                    }
                }

                // Si hay appointments de este mentor en este rango de tiempo se guarda tanto el mentor como el rango
                if (!empty($mentorAppointments)) {
                    $mentorsAdminFinished[] = [
                        'mentor' => $mentor,
                        'surveyRange' => $surveyRangeCheck
                    ];
                }
            }
        }

        $timeDif = [];
        $areas = [];
        foreach ($appointments['data'] as $appointment) {
            if ($appointment->getTimeFrom() != null and $appointment->getTimeTo() != null) {
                $dif = $appointment->getTimeFrom()->diff($appointment->getTimeTo());
                $timeDif[] = ['id' => $appointment->getId(), 'mentor' => $appointment->getUser()->getId(), 'hours' => $dif->h, 'minutes' => $dif->i];
            }

            $areas[] = ['id' => $appointment->getUser()->getId(), 'name' => $appointment->getArea()->getName()];
        }

        $timeDifMentor = [];
        foreach ($timeDif as $element) {
            $mentor = $element['mentor'];
            $hours = $element['hours'];
            $minutes = $element['minutes'];

            if (!isset($timeDifMentor[$mentor])) {
                $timeDifMentor[$mentor] = ['id' => $mentor, 'hours' => 0, 'minutes' => 0];
            }

            $timeDifMentor[$mentor]['hours'] += $hours;
            $timeDifMentor[$mentor]['minutes'] += $minutes;

            $timeDifMentor[$mentor]['hours'] += intval($timeDifMentor[$mentor]['minutes'] / 60);
            $timeDifMentor[$mentor]['minutes'] = $timeDifMentor[$mentor]['minutes'] % 60;
        }

        $areasMentor = [];
        foreach ($areas as $area) {
            $mentor = $area['id'];
            $areaName = $area['name'];

            if (!isset($areasMentor[$mentor])) {
                $areasMentor[$mentor] = ['id' => $mentor, 'name' => ''];
            }

            if ($areasMentor[$mentor]['name'] == '') {
                $areasMentor[$mentor]['name'] = $areaName;
            } else {
                $isAreaAlreadyIn = false;
                foreach (explode(' - ', $areasMentor[$mentor]['name']) as $allMentorAreaName) {
                    if ($areaName == $allMentorAreaName) {
                        $isAreaAlreadyIn = true;
                    }
                }
                if (!$isAreaAlreadyIn) $areasMentor[$mentor]['name'] .= ' - ' . $areaName;
            }
        }

        $documents = [];
        foreach ($this->clientHasDocumentRepository->findBy(['client' => $clientId]) as $clientHasDocument) {
            $documents[] = $clientHasDocument->getDocument();
        }

        $surveys = $this->clientHasDocumentRepository->findBy(['client' => $clientId]);
        $startupSurveys = [];
        foreach ($surveys as $survey) {
            if ($survey->getDocument()->isStartUpSurvey()) {
                $startupSurveys[] = $survey;
            }
        }

        $surveyRangeFilterService = new FilterService($this->getCurrentRequest());
        $surveyRangeFilterService->addOrderValue('endDate', 'ASC');
        $orderedAllSurveyRanges = $this->surveyRangeRepository->findSurveyRanges($surveyRangeFilterService);

        return $this->render('document/index.html.twig', [
            'totalResults' => count($mentorsFinished),
            'lastPage' => $appointments['lastPage'],
            'totalAmount' => count($mentors),
            'currentPage' => $this->filterService->page,
            'filterService' => $this->filterService,
            'appointments' => $appointments,
            'mentors' => $mentorsAdminFinished,
            'totalTime' => $timeDifMentor,
            'areas' => $areasMentor,
            'documents' => $documents,
            'surveys' => $startupSurveys,
            'client' => $this->clientRepository->find($clientId),
            'surveyRanges' => $orderedAllSurveyRanges['data'],
            'selectedSurveyRange' => $surveyRange
        ]);
    }
}
