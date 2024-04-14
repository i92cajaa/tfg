<?php


namespace App\Service\PermissionService;



use App\Entity\Config\Config;
use App\Entity\Config\ConfigType;
use App\Entity\Permission\Permission;
use App\Entity\Permission\PermissionGroup;
use App\Entity\Role\Role;
use App\Entity\User\User;
use App\Entity\User\UserHasPermission;
use App\Service\ConfigService\ConfigService;
use App\Shared\Classes\AbstractService;
use HttpResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PermissionGroupRepository;
use App\Repository\PermissionRepository;
use App\Repository\UserHasPermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Service\FilterService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PermissionService extends AbstractService
{


    public function __construct(
        private readonly UserHasPermissionRepository $userPermissionRepository,
        private readonly PermissionRepository $permissionRepository,
        private readonly PermissionGroupRepository $permissionGroupRepository,
        private readonly ConfigService $configService,

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


    /**
     * Retorna un permiso por su id.
     *
     * @param string $id
     * @return Permission|null
     */
    public function getPermissionById(string $id): ?Permission
    {
        return $this->permissionRepository->find($id);
    }

    public function addPermissionToUser(User $user, Permission $permission ): void
    {
        $this->userPermissionRepository->addUserPermission($user, $permission);
    }

    public function getPermissionByTaskAndGroupName(string $action, string $groupName){
        return $this->permissionRepository->getPermissionByTaskAndGroupName($action, $groupName);
    }

    /**
     * @param User $user
     * @param array|null|Permission[] $permissions
     */
    public function addPermissionArrayToUser(User $user, ?array $permissions = []) {
        foreach ($permissions as $permission) {
            $this->addPermissionToUser($user, $permission);
        }
    }

    public function can(string $action, string $group, ?array $permissions = null): ?bool
    {
        if ($permissions and @$permissions[$group]) {
            return @in_array($action, $permissions[$group]);
        }
        return false;
    }


    public function userPermissionMap(User $user): array
    {
        $permissions = [];
        foreach ($user->getPermissions() as $userPermission) {
            $permission                       = $userPermission->getPermission();
            $group                            = $permission->getGroup();
            $permissions[$group->getName()][] = $permission->getTask();
        }

        return $permissions;
    }

    public function userHasPermissionId(User $user, int $pId): bool
    {
        foreach ($user->getPermissions() as $permission) {
            if ($permission->getPermission()->getId() === $pId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Recupera todos los permisos disponibles en la plataforma ordenados por grupos de permisos
     */
    public function getAvailablePermissions()
    {
        if(in_array(Role::ROLE_SUPERADMIN, $this->getUser()->getRoleIds())){
            $permissions = $this->permissionGroupRepository->getAvailablePermission();
        }else{
            $permissions = $this->permissionGroupRepository->getAvailablePermission();
        }
        return $permissions;
    }

    /**
     * @param User $user
     * @param array $permissions
     * @return array|null
     */
    public function generatePermissionsArray(User $user, array $permissions = []):?array
    {
        $userPermissions = [];
        if (!$permissions) return $userPermissions;

        foreach ($permissions as $permission) {
            if(is_object($permission)) {
                $userPermissions[] = $this->userPermissionRepository->generateFromUserAndPermission($user, $permission);
            } else {
                $userPermissions[] = $this->userPermissionRepository->generateFromUserAndId($user, $permission);
            }

        }

        return $userPermissions;
    }

    public function getAll():?array
    {
        return $this->permissionRepository->findAll();
    }

}
