<?php

namespace App\EventListener;


use App\Annotation\Permission;
use App\Service\PermissionService\PermissionService;
use App\Service\UserService\UserPermissionService;
use Doctrine\Common\Annotations\Reader;
use ReflectionException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\UsageTrackingTokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class PermissionAnnotationListener
{


    public function __construct(
        private readonly Reader $reader,
        private readonly UserPermissionService $userPermissionService,
        private readonly Security $security
    )
    {

    }

    /**
     * @param ControllerEvent $event
     * @throws ReflectionException
     */
    public function onKernelController(ControllerEvent $event)
    {
        if (!is_array($controllers = $event->getController())) {
            return ;
        }

        list($controller, $methodName) = $controllers;

        $reflectionClass = new \ReflectionClass($controller);
        $classAnnotation = $this->reader
            ->getClassAnnotation($reflectionClass, Permission::class);

        $reflectionObject = new \ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        /** @var Permission $methodAnnotation */
        $methodAnnotation = $this->reader
            ->getMethodAnnotation($reflectionMethod, Permission::class);

        if (!($classAnnotation || $methodAnnotation)) {
            return;
        }

        $this->handleAnnotationTask($methodAnnotation, $event);

    }


    private function handleAnnotationTask(Permission $permission, ControllerEvent $event){
        $group = $permission->getGroup();
        $action = $permission->getAction();
        if(!$this->userPermissionService->can($action, $group)) {
            throw new AccessDeniedException();
        }

    }
}