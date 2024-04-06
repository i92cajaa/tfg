<?php

namespace App\Controller\PublicController;

use App\Service\MailService;
use App\Service\ClientRequestService\ClientRequestService;
use App\Service\UserService\UserService;
use App\Service\StripeService\StripeService;
use App\Service\TokenService\TokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/public')]
class PublicController extends AbstractController
{

    public function __construct(
        private readonly MailService $requestServiceMail,
        private readonly ClientRequestService $requestService,
        private readonly StripeService $stripeService,
        private readonly TokenService $tokenService,
        private readonly UserService $userService

    )
    {

    }

    #[Route(path: '/render-request-form', name: 'public_request_render_new', methods: ["GET", "POST"])]
    public function index(): Response
    {
        return $this->requestService->new();
    }


    #[Route(path: '/data-protec', name: 'data-protec', methods: ["GET"])]
    public function dataProtec(): Response
    {
        return $this->requestService->data();
    }

    #[Route(path: '/legal-sign', name: 'legal-sign', methods: ["GET"])]
    public function data(): Response
    {
        return $this->requestService->legalSign();
    }

    #[Route(path: '/success', name: 'success', methods: ["GET"])]
    public function success(): Response
    {
        return $this->requestService->success();
    }


    #[Route(path: '/webhook', name: 'webhook', methods: ["GET", "POST"])]
    public function webhook(Request $request):Response
    {
         return $this->stripeService->webhook($request);
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
    /*
    #[Route(path: '/azureToken', name: 'public_azure_token', methods: ["GET", "POST"])]
    public function azureToken(): JsonResponse
    {
        return $this->tokenService->newAzureAuthToken();
    }
    */

}
