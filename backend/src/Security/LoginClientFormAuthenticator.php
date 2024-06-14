<?php

namespace App\Security;

use Psr\Log\LoggerInterface;
use App\Entity\Client\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class LoginClientFormAuthenticator extends AbstractLoginFormAuthenticator implements AuthenticationEntryPointInterface
{
    public const LOGIN_ROUTE = 'app_login_client';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly LoggerInterface $logger
    )
    {
    }

    public function authenticate(Request $request): Passport
    {
        $id = $request->request->get('dni', '');
        $password = $request->request->get('password', '');

        $this->logger->info('Attempting authentication', ['dni' => $id]);

        $request->getSession()->set(Security::LAST_USERNAME, $id);

        return new Passport(
            new UserBadge($id),
            new PasswordCredentials($password),
            []
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): JsonResponse
    {
        $this->logger->info('Authentication successful', ['user' => $token->getUser()]);

        /** @var Client $client */
        $client = $token->getUser();

        $jwtToken = $this->jwtManager->create($client);

        return new JsonResponse([
            'token' => $jwtToken,
            'client_id' => $client->getId(),
            'message' => 'Authentication successful',
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $this->logger->error('Authentication failed', ['exception' => $exception->getMessage()]);

        return new JsonResponse([
            'message' => 'Authentication failed',
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $loginUrl = $this->getLoginUrl($request);

        return new RedirectResponse($loginUrl);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
