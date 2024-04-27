<?php


namespace App\Service\UserService;


use App\Entity\User\User;
use App\Service\PermissionService\PermissionService;
use App\Shared\Classes\AbstractService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class UserPermissionService extends AbstractService
{

    public function __construct(
        private readonly PermissionService $permissionService,

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
     * Función utilizada en la fachada para comprobar si el usuario actual tiene acceso al recurso solicitado.
     *
     * @param string $action
     * @param string $group
     * @return bool|null
     */
    public function can(string $action, string $group): ?bool
    {
        $permissions = $this->getSession()->get('permissions');

        return $this->permissionService->can($action, $group, $permissions);
    }

    public function userHasPermissionId(User $user, int $pId): bool
    {
        return $this->permissionService->userHasPermissionId($user,$pId);
    }
}
