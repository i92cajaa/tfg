<?php

namespace App\Service\UserService;

use App\Entity\User\UserHasRole;
use App\Repository\LessonRepository;
use App\Service\ScheduleService\ScheduleService;
use App\Entity\Status\Status;
use App\Entity\User\User;
use App\Entity\User\UserHasPermission;
use App\Form\UserPasswordUpdateType;
use App\Form\UserType;
use App\Repository\CenterRepository;
use App\Repository\ClientRepository;
use App\Repository\RoleRepository;
use App\Repository\StatusRepository;
use App\Repository\AreaRepository;
use App\Service\DocumentService\DocumentService;
use App\Service\PermissionService\PermissionService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use Twig\Environment;

class UserService extends AbstractService
{

    const UPLOAD_FILES_PATH = 'images/users';

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly PermissionService $permissionService,
        private readonly ScheduleService $scheduleService,
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly AreaRepository $areaRepository,
        private readonly CenterRepository $centerRepository,
        private readonly ClientRepository $clientRepository,
        private readonly StatusRepository $statusRepository,
        private readonly LessonRepository $lessonRepository,

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
     * EN: SERVICE TO GET ALL TEACHERS BY THE CENTER
     * ES: SERVICIO PARA OBTENER LOS PROFESORES DE UN CENTRO
     *
     * @return JsonResponse
     */
    // ----------------------------------------------------------------
    public function getByCenter(): JsonResponse
    {
        $this->filterService->addFilter('center', $this->getRequestPostParam('center'));
        $this->filterService->addFilter('roles', 3);
        $this->filterService->addFilter('status', true);

        $users = $this->userRepository->findUsers($this->filterService, true, true);

        return new JsonResponse(['users' => $users['users'], 'status' => true]);
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

        $this->filterService->addFilter('teacher', $user->getId());

        return $this->render('user/show.html.twig', [
            'currentPage' => $this->filterService->page,
            'user' => $user,
            'clients' => $this->clientRepository->findAll(),
            'filterService' => $this->filterService,
            'status' => $this->statusRepository->findAll(),
            'permissions' => $this->permissionService->getAvailablePermissions(),
            'areas' => $this->areaRepository->findAreas($this->filterService, true),
            'centers' => $this->centerRepository->findCenters($this->filterService, true),
            'lessons' => $this->lessonRepository->findLessons($this->filterService, true)
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
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function user_view_profile(string $userId, Request $request): Response
    {
        $user = $this->userRepository->findById($userId, false);
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

        return $this->render('user/show_profile.html.twig', [
            'currentPage' => $this->filterService->page,
            'user' => $user,
            'form' => $formView,
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
    public function new(): Response
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

                $role = $form->get('role')->getData();
                if ($role != null) {
                    $user->addRole((new UserHasRole())->setUser($user)->setRole($role));
                    foreach ($role->getPermissions() as $roleHasPermission) {
                        $userHasPermission = (new UserHasPermission())->setUser($user)->setPermission($roleHasPermission->getPermission());
                        $user->addPermission($userHasPermission);
                    }
                }

                $center = $form->get('center')->getData();
                if ($center != null) {
                    $user->setCenter($center);
                }

                if ($form->get('password')->getData() != null) {
                    $this->userRepository->upgradePassword($user, $form->get('password')->getData());
                }

                $file = $form->get('img_profile')->getData();
                $this->userRepository->persist($user);
                if ($file != null) {
                    $imgProfile = $this->documentService->uploadDocument($file, self::UPLOAD_FILES_PATH);
                    $user->setImgProfile($imgProfile);
                    $this->userRepository->persist($user);
                }

                $this->getSession()->getFlashBag()->add('success', 'Usuario creado correctamente.');
                return $this->redirectToRoute('user_index');
            } catch (\Exception $error) {
                $this->getSession()->getFlashBag()->add('danger', 'Error al crear nuevo usuario.');
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
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
        $user = $this->userRepository->findById($userId, false);

        if (!$user) {
            throw new \Exception("User not found");
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($this->getCurrentRequest());
        $permissions = $this->permissionService->getAvailablePermissions();

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(UTCDateTime::create('NOW'));

            $userExists = $this->userRepository->findOneBy(['email' => $form->get('email')->getData()]);
            if ($userExists and $userExists->getId() != $user->getId()) {
                $this->addFlash("error", $this->translate('User with this email already exists') . " " . $userExists->getEmail());
                return $this->redirectToRoute('user_edit', ['user' => $userId]);
            } elseif (!$userExists) {
                $user->setEmail($form->get('email')->getData());
            }

            if ($form->get('password')->getData() != $user->getPassword() && $form->get('password')->getData() != null && $form->get('password')->getData() != "") {
                $this->userRepository->upgradePassword($user, $form->get('password')->getData());
            }

            $center = $form->get('center')->getData();
            if ($center != null) {
                $user->setCenter($center);
            }

            $role = $form->get('role')->getData();
            if ($role != null) {
                if ($role->getName() != $user->getRoles()[0]) {
                    $this->userRepository->removeAllRoles($user);
                    $user->addRole((new UserHasRole())->setRole($role)->setUser($user));
                    $this->userRepository->removeAllPermissions($user);
                    foreach ($role->getPermissions() as $roleHasPermission) {
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
        $form = $this->createForm(UserPasswordUpdateType::class, null);
        $form->handleRequest($this->getCurrentRequest());
        $formView = $form->createView();

        $user = $this->userRepository->findUserByToken($token);
        if (!$user) {

            return $this->render('user/changePasswordScreen.html.twig', [
                'token' => $token,
                'isPasswordValid' => true,
                'isRepeatPasswordValid' => true,
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
            'isPasswordValid' => true,
            'isRepeatPasswordValid' => true,
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
     * @throws NonUniqueResultException
     */
    // ----------------------------------------------------------------
    public function change_status(string $user): Response
    {
        $user = $this->userRepository->findById($user, false);

        try {
            if ($user->getStatus()) {
                $this->userRepository->changeStatus($user, false);
                if ($user->isTeacher()){
                    foreach ($user->getLessons() as $userHasLesson) {
                        $lesson = $userHasLesson->getLesson();
                        $lesson->setStatus(false);

                        foreach ($lesson->getSchedules() as $schedule) {
                            if ($schedule->getDateFrom() > UTCDateTime::create('NOW')) {
                                $this->scheduleService->changeStatus($schedule->getId(), Status::STATUS_CANCELED);
                            }
                        }
                    }
                }
                $this->getSession()->getFlashBag()->add('success', 'Usuario desactivado correctamente.');
            } else {
                $this->userRepository->changeStatus($user, true);
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
     * EN: SERVICE TO DELETE AN USER
     * ES: SERVICIO PARA BORRAR UN USUARIO
     *
     * @param string $user
     * @return Response
     */
    // ----------------------------------------------------------------
    public function delete(string $user): Response
    {
        $user = $this->getEntity($user);

        try {
            if (count($user->getLessons()) == 0) {
                $this->userRepository->remove($user);
                $this->getSession()->getFlashBag()->add('success', 'Usuario borrado correctamente.');
            } else {
                $this->getSession()->getFlashBag()->add('success', 'Se ha desactivado el usuario, ya que tiene clases asignadas');
                $this->userRepository->changeStatus($user, false);
            }
        } catch (\Exception $error) {
            $this->getSession()->getFlashBag()->add('danger', 'Error al eliminar el usuario');
        }

        return $this->redirectToRoute('user_index');
    }
    // ----------------------------------------------------------------
}
