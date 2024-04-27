<?php


namespace App\Controller\NotificationController;


use App\Annotation\Permission;

use App\Service\NotificationService\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/notification')]
class NotificationController extends AbstractController
{

    public function __construct(
        private readonly NotificationService $notificationService
    )
    {
    }

    #[Route(path: '/check', name: 'notification_read', methods: ["POST"])]
    public function checkNotificationSeen(): JsonResponse
    {
        $this->notificationService->checkNotification();
        return new JsonResponse(["success" => true]);
    }

    /*Funcion para borrrar la notificacion que esta activa*/
    #[Route(path: '/delete', name: 'notification_delete', methods: ["POST"])]
    public function deleteNotification(): JsonResponse
    {
        return $this->notificationService->deleteNotification();
    }

    /*Funcion para borrrar todas las notificaciones que esten activas y pertenezcan a un profesional*/
    #[Route(path: '/deleteAll', name: 'notification_all_delete', methods: ["POST"])]
    public function deleteAllNotification(): JsonResponse
    {
        return $this->notificationService->deleteAllNotification();
    }

}