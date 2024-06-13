<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class JwtTokenListener
{
    public function __construct(private JWTTokenManagerInterface $jwtManager)
    {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $jwtToken = $request->headers->get('Authorization');

        if (!$jwtToken || strpos($jwtToken, 'Bearer ') !== 0) {
            return; // Token not found or invalid format
        }

        $token = substr($jwtToken, 7); // Remove 'Bearer ' prefix

        try {
            $decodedToken = $this->jwtManager->decode($token);
            // Attach user or relevant data to the request
            $request->attributes->set('user', $decodedToken['username']); // Example: Set user to request attribute
        } catch (\Exception $e) {
            // Handle invalid token
            return; // Or throw an exception
        }
    }
}