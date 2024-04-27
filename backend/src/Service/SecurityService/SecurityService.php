<?php


namespace App\Service\SecurityService;


use App\Entity\Service\Service;
use App\Entity\User\User;
use App\Repository\DivisionRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\RoleRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SecurityService extends AbstractService
{

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;


    public function __construct(
        private readonly AuthenticationUtils $authenticationUtils,

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

        $this->userRepository = $em->getRepository(User::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator
        );
    }

    public function login(bool $isClient = false): Response
    {
        if ($isClient and $this->getUser()) {
            //$this->getSession()->set('permissions',$this->getUser()->getPermissionsArray());
            return $this->redirectToRoute('area_index');
        }

        if ($this->getUser()) {
            $this->getSession()->set('permissions',$this->getUser()->getPermissionsArray());
            return $this->redirectToRoute('area_index');
        }

        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', ['last_email' => $lastUsername, 'error' => $error]);
    }

   

    public function changeLocale(string $locale): Response
    {
        $user = $this->getUser();

        if($user){
            $user->setLocale($locale);

            if($user instanceof User){
                $this->userRepository->persist($user);
            }
        }

        $this->getSession()->set('locale', $locale);
        setrawcookie('locale', $locale, [
            'expires' => time() + 86400,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None',
        ]);

        return $this->redirectBack();

    }

    

    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


}