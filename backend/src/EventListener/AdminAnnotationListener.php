<?php

namespace App\EventListener;


use App\Annotation\Admin;
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

class AdminAnnotationListener
{

    public function __construct(
        private readonly Reader $reader,
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
            ->getClassAnnotation($reflectionClass, Admin::class);

        $reflectionObject = new \ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        /** @var Admin $methodAnnotation */
        $methodAnnotation = $this->reader
            ->getMethodAnnotation($reflectionMethod, Admin::class);

        if (!($classAnnotation || $methodAnnotation)) {
            return;
        }

        $this->handleAnnotationTask($methodAnnotation, $event);

    }


    private function handleAnnotationTask(Admin $admin, ControllerEvent $event){
        if(!$this->security->getUser()->isAdmin()) {
            throw new AccessDeniedException();
        }

    }
}