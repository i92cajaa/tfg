<?php


namespace App\Twig\Extension;


use App\Service\PermissionService\PermissionService;
use App\Service\UserService\UserPermissionService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class UserPermissionExtension extends AbstractExtension implements GlobalsInterface
{


    public function __construct(

        private readonly UserPermissionService $userPermissionService
    )
    {

    }

    public function getGlobals():array
    {

        return [
            'userPermission' => $this->userPermissionService
        ];
    }
}