<?php
namespace App\Service\MeetingService;

use App\Entity\Appointment\Appointment;
use App\Entity\Meeting\Meeting;
use App\Entity\Token\Token;
use App\Repository\MeetingRepository;
use App\Repository\TokenRepository;
use App\Service\MicrosoftGraphsService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MeetingService extends AbstractService
{

    const UPLOAD_FILES_PATH = 'tokens';

    private MeetingRepository $meetingRepository;

    public function __construct(
        private readonly MicrosoftGraphsService $microsoftGraphsService,
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
        $this->meetingRepository = $em->getRepository(Meeting::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->meetingRepository
        );
    }

    public function createMeeting(
        Appointment $appointment,

    ): ?Meeting
    {
        $meeting = null;
        $subject = $this->translate('Appointment') . ': ' . $appointment->getClient()->getFullName();

        $this->microsoftGraphsService->init();

        $meetingOptions = $this->microsoftGraphsService->createMeeting(
            $appointment->getTimeFrom(false),
            $appointment->getTimeTo(false),
            $subject
        );

        if($meetingOptions){
            $meeting = $this->meetingRepository->createMeeting(
                $appointment,
                $subject,
                $meetingOptions['joinUrl'],
                $meetingOptions['joinWebUrl'],
                $meetingOptions['meetingCode'],
                $meetingOptions
            );
        }

        return $meeting;
    }



}