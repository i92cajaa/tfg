<?php
// JwtTokenListener.php

namespace App\EventListener;

use App\Entity\Client\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\Token\JWTPostAuthenticationToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JwtTokenListener
{
    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(JWTTokenManagerInterface $jwtManager, TokenStorageInterface $tokenStorage)
    {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // Retrieve the JWT token from the request
        $jwtString = $event->getRequest()->headers->get('Authorization');
        if (strpos($jwtString, 'Bearer ') === 0) {
            $jwtString = substr($jwtString, 7);
        }

        // Validate and decode the JWT token
        try {
            $decodedToken = $this->jwtManager->decode($this->tokenStorage->getToken());
            // Assuming $decodedToken is an array with user information
            $client = (new Client())->setDni($decodedToken['dni']);

            // Create a JWTPostAuthenticationToken
            $token = new JWTPostAuthenticationToken($client, 'client', ['ROLE_CLIENT'], $jwtString);

            // Store the token in the security context
            $this->tokenStorage->setToken($token);

            // Proceed with the request
        } catch (\Exception $e) {
            // Handle token validation or decoding errors
        }
    }
}
