<?php

namespace App\Controller\SecurityController;

use App\Security\LoginClientFormAuthenticator;
use App\Service\SecurityService\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    public function __construct(
        private readonly SecurityService $securityService,
        private readonly CsrfTokenManagerInterface $csrfTokenManager
    )
    {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->securityService->login();
    }

    #[Route(path: '/client/login', name: 'app_login_client')]
    public function clientLogin(): Response
    {
        return $this->securityService->clientLogin();
    }

    #[Route(path: '/change-locale/{locale}', name: 'app_change_locale', requirements: ['locale' => 'en|fr|de|es|pt'])]
    public function changeLocale(string $locale): Response
    {
        return $this->securityService->changeLocale($locale);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        
        $this->securityService->logout();
    }

    #[Route('/client/get-csrf-token', name: 'get_csrf_token')]
    public function getCsrfToken(): JsonResponse
    {
        $csrfToken = $this->csrfTokenManager->getToken('authenticate')->getValue();
        return new JsonResponse(['csrf_token' => $csrfToken]);
    }
   

}
