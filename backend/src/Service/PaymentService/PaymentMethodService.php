<?php


namespace App\Service\PaymentService;



use App\Entity\Payment\PaymentMethod;
use App\Form\PaymentMethodType;
use App\Repository\PaymentMethodRepository;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PaymentMethodService extends AbstractService
{


    /**
     * @var PaymentMethodRepository
     */
    private PaymentMethodRepository $paymentRepository;

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
        $this->paymentRepository = $em->getRepository(PaymentMethod::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->paymentRepository
        );
    }

    public function findAll(): array
    {
        return $this->paymentRepository->findAll();
    }

    public function index(): Response
    {
        $payments = $this->paymentRepository->findPaymentMethods($this->filterService);

        return $this->render('payment_method/index.html.twig', [
            'totalResults' => $payments['totalRegisters'],
            'lastPage' => $payments['lastPage'],
            'currentPage' => $this->filterService->page,
            'payments' => $payments['data'],
            'filterService' => $this->filterService
        ]);
    }

    public function create(): RedirectResponse|Response
    {
        $payment = new PaymentMethod();
        $form = $this->createForm(PaymentMethodType::class, $payment);

        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->paymentRepository->persist($payment);

            return $this->redirectToRoute('payment_method_index');
        }

        return $this->render('payment_method/new.html.twig', [
            'payment' => $payment,
            'form' => $form->createView()
        ]);
    }

    public function edit(string $payment): RedirectResponse|Response
    {
        $payment = $this->getEntity($payment);

        $form = $this->createForm(PaymentMethodType::class, $payment);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->paymentRepository->persist($payment);

            return $this->redirectToRoute('payment_method_index');
        }

        if( $form->isSubmitted() && !$form->isValid()){
            $this->addFlash('ERROR', $this->translate('The payment method already exists'));
        }

        return $this->render('payment_method/edit.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    public function activatePaymentMethodsByIds(array $ids){
        $this->paymentRepository->activatePaymentMethodsByIds($ids);
    }

    public function delete(string $payment): RedirectResponse
    {
        $payment = $this->getEntity($payment);

        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $this->getRequestPostParam('_token'))) {
            $this->paymentRepository->remove($payment);
        }

        return $this->redirectToRoute('payment_method_index');
    }
}