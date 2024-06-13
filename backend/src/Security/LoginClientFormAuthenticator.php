<?php

namespace App\Security;

use App\Entity\Client\Client;
use App\Entity\User\User;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class LoginClientFormAuthenticator extends AbstractLoginFormAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login_client';

    public function __construct(
        private readonly UrlGeneratorInterface  $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly JWTTokenManagerInterface $jwtManager
    )
    {
    }

    public function authenticate(Request $request): Passport
    {
        $id = $request->request->get('dni', '');

        $request->getSession()->set(Security::LAST_USERNAME, $id);

        return new Passport(
            new UserBadge($id),
            new PasswordCredentials($request->request->get('password', '')),
            [
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): JsonResponse
    {
        /** @var Client $user */
        $client = $token->getUser();

        $this->entityManager->persist($client);

        $jwtToken = $this->jwtManager->create($client);

        // For example:
        return new JsonResponse([
            'token' => $jwtToken,  // Replace with your actual token value
            'message' => 'Authentication successful',
        ]);
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
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
