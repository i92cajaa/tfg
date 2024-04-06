<?php


namespace App\Service\PaymentService;


use App\Entity\Appointment\AppointmentLog;
use App\Entity\Config\Config;
use App\Entity\Config\ConfigType;
use App\Entity\Payment\Payment;
use App\Entity\Payment\PaymentMethod;
use App\Form\PaymentType;
use App\Repository\AppointmentLogRepository;
use App\Repository\AppointmentRepository;
use App\Repository\ClientRepository;
use App\Repository\ConfigRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\PaymentRepository;
use App\Service\AppointmentService\AppointmentService;
use App\Service\ClientService\ClientService;
use App\Service\DocumentService\DocumentService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Utils\PdfCreator;
use App\Shared\Utils\Util;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PaymentService extends AbstractService
{

    const UPLOAD_FILES_PATH = 'payments';
    
    /**
     * @var PaymentRepository
     */
    private PaymentRepository $paymentRepository;


    /**
     * @var AppointmentLogRepository
     */
    private AppointmentLogRepository $appointmentLogRepository;
    /**
     * @var PaymentMethodRepository
     */
    private PaymentMethodRepository $paymentMethodRepository;
    /**
     * @var ConfigRepository
     */
    private ConfigRepository $configRepository;


    public function __construct(
        private readonly DocumentService $documentService,
        private readonly ClientService $clientService,
        private readonly AppointmentService $appointmentService,

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
        $this->paymentRepository = $em->getRepository(Payment::class);
        $this->configRepository = $em->getRepository(Config::class);
        $this->paymentMethodRepository = $em->getRepository(PaymentMethod::class);
        $this->appointmentLogRepository = $em->getRepository(AppointmentLog::class);


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

    public function getCreatePaymentTemplate(): JsonResponse
    {
        if ($this->isCsrfTokenValid('get-create-payment-template', $this->getRequestPostParam('_token'))) {

            $appointment = $this->appointmentService->find(@$this->getRequestPostParam('appointment'));
            $form = $this->createForm(PaymentType::class, new Payment());

            if($appointment){
                $template = $this->renderView('payment/create_template.html.twig', [
                    'appointment' => $appointment,
                    'payment_methods' => $this->paymentMethodRepository->findBy(['active' => true]),
                    'clients' => $this->clientService->findBy(['status' => true]),
                    'form' => $form->createView()
                ]);

                return new JsonResponse(['success' => true, 'data' => $template, 'message' => 'Plantilla de creación de entrada de historial']);
            }else{
                return new JsonResponse(['success' => false, 'message' => 'No se ha encontrado el pago']);
            }

        }
        return new JsonResponse(['success' => false, 'message' => 'El token no es válido']);
    }

    public function getEditPaymentTemplate(): JsonResponse
    {
        if ($this->isCsrfTokenValid('get-edit-payment-template', $this->getRequestPostParam('_token'))) {

            $payment = $this->paymentRepository->find($this->getRequestPostParam('payment'));
            $form = $this->createForm(PaymentType::class, new Payment());
            if($payment){
                $template = $this->renderView('payment/edit_template.html.twig', [
                    'payment' => $payment,
                    'payment_methods' => $this->paymentMethodRepository->findBy(['active' => true]),
                    'clients' => $this->clientService->findBy(['status' => true]),
                    'form' => $form->createView()
                ]);

                return new JsonResponse(['success' => true, 'data' => $template, 'message' => 'Plantilla de creación de pago']);
            }else{
                return new JsonResponse(['success' => false, 'message' => 'No se ha encontrado el pago']);
            }

        }
        return new JsonResponse(['success' => false, 'message' => 'El token no es válido']);
    }

    public function editPaymentByRequest(string $payment): RedirectResponse
    {
        /** @var Payment $payment */
        $payment = $this->getEntity($payment);
        if ($this->isCsrfTokenValid('edit-payment', $this->getRequestPostParam('_token'))) {

            $form = $this->createForm(PaymentType::class, $payment);
            $form->handleRequest($this->getCurrentRequest());

            if ($form->isSubmitted() && $form->isValid()) {
                $clientData = $form->getExtraData()['client'];
                if (is_array($clientData)){
                    $clientExists = null;
                    if(@$clientData['email']) {
                        $clientExists = $this->clientService->checkIfClientExist($clientData['email']);

                    }
                    if($clientExists){
                        $payment->setClient($clientExists);

                    }else{
                        $newClient = $this->clientService->createClient($clientData);

                        if($newClient != null){
                            $payment->setClient($newClient);
                        }else{
                            $clientNomenclature = $this->configRepository->findOneBy(['tag' => ConfigType::CLIENT_NOMENCLATURE_TAG]);
                            $clientStr = $clientNomenclature ? $clientNomenclature->getValue() : 'client';
                            $this->addFlash('error', $this->translate("No $clientStr found to assign payment to"));
                            return $this->redirectBack();
                        }
                    }

                }else{
                    $payment->setClient($this->clientService->find($clientData));
                }

                $payment->setPaymentDate(UTCDateTime::create('d-m-Y H:i',  $form->getExtraData()['payment_date']));

                $this->paymentRepository->persist($payment);

                $this->appointmentService->checkPaidAppointment($payment->getAppointment());
            }

            $this->addFlash('success', $this->translate('Payment has been successfully edited'));
        }

        return $this->redirectBack();
    }

    public function createPaymentByRequest(): RedirectResponse
    {
        if ($this->isCsrfTokenValid('create-payment', $this->getRequestPostParam('_token'))) {

            $payment = new Payment();
            $form = $this->createForm(PaymentType::class, $payment);
            $form->handleRequest($this->getCurrentRequest());


            if ($form->isSubmitted() && $form->isValid()) {
                $clientData = $form->getExtraData()['client'];
                if (is_array($clientData)){
                    $clientExists = null;
                    if(@$clientData['email']) {
                        $clientExists = $this->clientService->checkIfClientExist($clientData['email']);

                    }

                    if($clientExists){

                        $payment->setClient($clientExists);

                    }else{
                        $newClient = $this->clientService->createClient($clientData);

                        if($newClient != null){
                            $payment->setClient($newClient);
                        }else{
                            $clientNomenclature = $this->configRepository->findOneBy(['tag' => ConfigType::CLIENT_NOMENCLATURE_TAG]);
                            $clientStr = $clientNomenclature ? $clientNomenclature->getValue() : 'client';
                            $this->addFlash('error', $this->translate("No $clientStr found to assign payment to"));
                            return $this->redirectBack();
                        }
                    }

                }else{
                    $payment->setClient($this->clientService->find($clientData));
                }
                $payment->setPaymentDate(UTCDateTime::create('d-m-Y H:i',  $form->getExtraData()['payment_date']));

                $this->paymentRepository->persist($payment);

                $this->appointmentLogRepository->createAppointmentLog(
                    $payment->getAppointment(),
                    $this->getUser(),
                    AppointmentLog::JOB_NEW_PAYMENT,
                    'A payment has been made for this appointment'
                );

                $this->appointmentService->checkPaidAppointment($payment->getAppointment());
            }


            $this->addFlash('success', $this->translate('Payment has been successfully created'));
        }

        return $this->redirectBack();
    }

    public function deletePayment(string $payment): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete', $this->getRequestPostParam('_token'))) {
            $payment = $this->getEntity($payment);
            $this->paymentRepository->remove($payment);
        }

        return $this->redirectBack();
    }

}