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
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocaleListener
{


    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator
    )
    {

    }

    public function onKernelRequest(RequestEvent $event)
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        $locale = $this->requestStack->getCurrentRequest()->getSession()->get('locale') ?: @$_COOKIE['locale'] ?: null;

        if($locale) {
            $this->translator->setLocale(strtolower($locale));
        }elseif($user){
            $this->translator->setLocale(strtolower($user->getLocale()));
        }

    }
}