<?php

namespace App\Controller\SecurityController;

use App\Service\SecurityService\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    public function __construct(
        private readonly SecurityService $securityService
    )
    {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(): Response
    {
        return $this->securityService->login();
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

    

   

}
