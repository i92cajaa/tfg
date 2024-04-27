<?php


namespace App\Service\NotificationService;



use App\Entity\Notification\Notification;
use App\Entity\User\User;
use App\Repository\ConfigRepository;
use App\Repository\DocumentRepository;
use App\Repository\NotificationRepository;
use App\Service\DocumentService\DocumentService;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class NotificationService extends AbstractService
{

    /**
     * @var NotificationRepository
     */
    private NotificationRepository $notificationRepository;


    public function __construct(
        EntityManagerInterface $em,

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
        $this->notificationRepository = $em->getRepository(Notification::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->notificationRepository
        );
    }

    public function getUnSeenNotificationsByUser(User $user): array
    {
        return $this->notificationRepository->findBy(['user' => $user, 'seen' => false]);
    }

    public function createNotification(string $messageText, ?string $link, User $user): Notification
    {
        return $this->notificationRepository->createNotification(
            $messageText,
            $link,
            $user
        );
    }

    public function checkNotification(): Notification
    {
        $notification = $this->notificationRepository->find($this->getRequestPostParam('id'));
        return $this->notificationRepository->checkNotification(
            $notification
        );
    }

    public function deleteNotification(): JsonResponse
    {

        $notification = $this->notificationRepository->find($this->getRequestPostParam('id'));

        $this->notificationRepository->deleteNotification(
            $notification
        );

        return new JsonResponse(["success" => true]);
    }

    public function deleteAllNotification(): JsonResponse
    {

        $notification = $this->notificationRepository->find($this->getRequestPostParam('userId'));

        $this->notificationRepository->deleteNotification(
            $notification
        );

        return new JsonResponse(["success" => true]);
    }


}