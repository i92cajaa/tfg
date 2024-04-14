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
use Doctrine\ORM\NonUniqueResultException;
use Exception;
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

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly PermissionService $permissionService,
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly AreaRepository $areaRepository,
        private readonly CenterRepository $centerRepository,
        private readonly ClientRepository $clientRepository,
        private readonly StatusRepository $statusRepository,
        private readonly DocumentRepository $documentRepository,
        private readonly ClientHasDocumentRepository $clientHasDocumentRepository,

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
            $this->userRepository
        );
    }

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO LIST ALL USERS
     * ES: SERVICIO PARA LISTAR TODOS LOS USUARIOS
     *
     * @return Response
     */
    // ----------------------------------------------------------------
    public function index(): Response
    {
        $users = $this->userRepository->findUsers($this->filterService);
        $roles = $this->roleRepository->findAll();

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
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW AN USER'S DATA
     * ES: SERVICIO PARA MOSTRAR LOS DATOS DE UN USUARIO
     *
     * @param string $userId
     * @return Response
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function show(string $userId): Response
    {
        $user = $this->userRepository->findById($userId, false);

        return $this->render('user/show.html.twig', [
            'currentPage' => $this->filterService->page,
            'user' => $user,
            'clients' => $this->clientRepository->findAll(),
            'filterService' => $this->filterService,
            'status' => $this->statusRepository->findAll(),
            'permissions' => $this->permissionService->getAvailablePermissions(),
            'areas' => $this->areaRepository->findAreas($this->filterService, true),
            'centers' => $this->centerRepository->findCenters($this->filterService, true)
        ]);
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO SHOW AN USER'S PROFILE
     * ES: SERVICIO PARA MOSTRAR EL PERFIL DE UN USUARIO
     *
     * @param string $userId
     * @param Request $request
     * @return Response
     */
    // ----------------------------------------------------------------
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
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CREATE A NEW USER
     * ES: SERVICIO PARA CREAR UN USUARIO NUEVO
     *
     * @return Response
     */
    // ----------------------------------------------------------------
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
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO EDIT AN USER'S DATA
     * ES: SERVICIO PARA EDITAR LOS DATOS DE UN USUARIO
     *
     * @param string $userId
     * @return RedirectResponse|Response
     * @throws Exception
     */
    // ----------------------------------------------------------------
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
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CHANGE AN USER'S PASSWORD
     * ES: SERVICIO PARA CAMBIAR LA CONTRASEÑA DE UN USUARIO
     *
     * @param string $token
     * @param Request $request
     * @return Response
     */
    // ----------------------------------------------------------------
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
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO CHANGE AN USER'S STATUS
     * ES: SERVICIO PARA CAMBIAR EL ESTADO DE UN USUARIO
     *
     * @param string $user
     * @return Response
     */
    // ----------------------------------------------------------------
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
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: SERVICE TO UPLOAD A DOCUMENT TO AN USER
     * ES: SERVICIO PARA SUBIR UN DOCUMENTO A UN USUARIO
     *
     * @param string $user
     * @return Response
     */
    // ----------------------------------------------------------------
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
    // ----------------------------------------------------------------
}
