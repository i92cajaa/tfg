<?php
namespace App\Service\TokenService;

use App\Entity\Token\Token;
use App\Repository\TokenRepository;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TokenService extends AbstractService
{

    const UPLOAD_FILES_PATH = 'tokens';

    private TokenRepository $tokenRepository;

    public function __construct(

        EntityManagerInterface $em,

        RouterInterface       $router,
        Environment           $twig,
        RequestStack          $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface      $tokenManager,
        FormFactoryInterface           $formFactory,
        SerializerInterface            $serializer,
        TranslatorInterface $translator
    )
    {
        $this->tokenRepository = $em->getRepository(Token::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->tokenRepository
        );
    }

    public function findTokenValueByTagAndType(string $tag, string $type): ?string
    {
        $token = $this->tokenRepository->findOneBy(['tag' => $tag, 'tokenType' => $type]);
        $actualDateTime = UTCDateTime::create(null, null, new \DateTimeZone('UTC'));
        if($token && ($token->getExpirationDate() > $actualDateTime && $token->getExtExpirationDate() > $actualDateTime)){
            return $token->getToken();
        }
        return null;
    }

    /*
    public function newAzureAuthToken() :JsonResponse
    {
        $expirationDate = (new \DateTime('now'))->modify('+1 hour');

        $this->tokenRepository->createOrUpdateToken(
            Token::AZURE_AUTHENTICATION_TAG,
            Token::AZURE_AUTHENTICATION_TOKEN_TYPE,
            $expirationDate,
            $expirationDate,
            @$this->getRequestParam('code')
        );

        return new JsonResponse(['Success' => true]);

    }
    */

    public function newAzureAccessToken(
        ?int $expirationDateSeconds,
        ?int $extExpirationDateSeconds,
        ?string $token
    ) :Token
    {
        $expirationDate = UTCDateTime::create()->modify('+'. intval($expirationDateSeconds) .' seconds');
        $extExpirationDate = UTCDateTime::create()->modify('+'. intval($extExpirationDateSeconds) .' seconds');

        return $this->tokenRepository->createOrUpdateToken(
            Token::AZURE_ACCESS_TAG,
            Token::AZURE_ACCESS_TOKEN_TYPE,
            $expirationDate,
            $extExpirationDate,
            $token
        );

    }


}