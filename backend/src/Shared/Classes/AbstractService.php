<?php

namespace App\Shared\Classes;

use App\Service\FilterService;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractService
{

    protected FilterService $filterService;

    public function __construct(
        protected readonly RequestStack        $requestStack,
        private readonly RouterInterface       $router,
        private readonly Environment           $twig,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly CsrfTokenManagerInterface      $tokenManager,
        private readonly FormFactoryInterface           $formFactory,
        private readonly SerializerInterface            $serializer,
        private readonly TranslatorInterface $translator,
        private readonly ?EntityRepository $entityRepository = null
    )
    {
        if($this->requestStack->getCurrentRequest()){
            $this->filterService = new FilterService($requestStack->getCurrentRequest());
        }

    }

    protected function getEntity(?string $entityId, bool $required = true)
    {
        $entity = $entityId ? $this->entityRepository->find($entityId) : null;
        if($required && !$entity){
            throw new BadRequestException();
        }

        return $entity;

    }

    protected function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    protected function getCurrentTimezone(): bool|float|int|string
    {
        return @$this->requestStack->getCurrentRequest()->cookies->get('timezone') ?: 'Europe/Madrid';
    }

    protected function createDateTimeFromFormat(string $format, string $datetime): DateTime|bool
    {
        return (UTCDateTime::create($format, $datetime))->setTimezone((new \DateTimeZone($this->getCurrentTimezone())));
    }

    protected function translate(string $text, ?string $locale = null): string
    {
        return $this->translator->trans($text, [], null, $locale);
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }


    protected function getCurrentRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    protected function getRequestParam(string $paramName){
        return @$this->getCurrentRequest()->get($paramName);
    }

    protected function getRequestPostParam(string $paramName){
        return @$this->getCurrentRequest()->request->all()[$paramName];
    }

    protected function getUser(): ?UserInterface
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        return $token->getUser();
    }

    protected function isCsrfTokenValid(string $id, ?string $token): bool
    {
        return $this->tokenManager->isTokenValid(new CsrfToken($id, $token));
    }

    protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface
    {
        return $this->formFactory->create($type, $data, $options);
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $content = $this->renderView($view, $parameters);

        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }

    protected function renderView(string $view, array $parameters = []): string
    {
        return $this->twig->render($view, $parameters);
    }

    protected function addFlash(string $type, mixed $message): void
    {
        try {
            $this->requestStack->getSession()->getFlashBag()->add($type, $this->translate($message));
        } catch (SessionNotFoundException $e) {
            throw new \LogicException('You cannot use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".', 0, $e);
        }
    }

    protected function json(mixed $data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {

        $json = $this->serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);

    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    protected function redirectBack(): RedirectResponse
    {
        $url = $this->getCurrentRequest()->headers->get('referer');
        return $this->redirect($url);
    }

    protected function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }
}