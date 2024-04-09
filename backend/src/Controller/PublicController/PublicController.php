<?php

namespace App\Controller\PublicController;

use App\Service\ClientService\ClientService;
use App\Service\MailService;
use App\Service\UserService\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/public')]
class PublicController extends AbstractController
{

    public function __construct(
        private readonly MailService $requestServiceMail,
        private readonly UserService $userService,
        private readonly ClientService $requestService

    )
    {

    }

    #[Route(path: '/remember-password', name: 'remember_password')]
    public function rememberPassword(): Response
    {

        return $this->requestService->rememberPassword();
    }

    #[Route(path: '/change-password/{token}', name: 'change_password')]
    public function changePassword(string $token,Request $request): Response
    {
        
        return $this->userService->changePassword($token,$request);
    }

    #[Route(path: '/remember-password-request', name: 'remember_password_request')]
    public function rememberPasswordRequest(Request $request)
    {
        
        $response =  $this->requestServiceMail->rememberPassword($request);

        if ($response) {
            $this->addFlash('success', 'Se ha enviado un correo para restablecer la contraseña.');
        } else {
            $this->addFlash('error', 'Este correo no está registrado con ningún usuario.');
        }
        return $this->requestService->rememberPassword();
    }

}
