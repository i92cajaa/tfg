<?php


namespace App\EventListener;


use App\Entity\User\User;
use App\Service\NotificationService\NotificationService;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\Translator;

class NotificationListener
{


    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
        private readonly NotificationService $notificationService)
    {

    }

    public function onKernelController(ControllerEvent $event)
    {
        $user = $this->security->getUser();
        if($user && $user instanceof User) {
            $notifications = $this->notificationService->getUnSeenNotificationsByUser($user);
//            if($notifications){
//                $message = 'Tiene '. sizeof($notifications) . ' notificaciones sin leer';
//
//                if(!in_array($message, $this->requestStack->getSession()->getFlashBag()->get('notice'))){
//                    $this->requestStack->getSession()->getFlashBag()->add('notice', $message);
//                }
//            }

        }


        // inspect the exception
        // do whatever else you want, logging, modify the response, etc, etc
    }
}