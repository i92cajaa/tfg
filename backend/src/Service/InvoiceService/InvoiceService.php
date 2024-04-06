<?php


namespace App\Service\InvoiceService;


use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\Config\ConfigType;
use App\Entity\Invoice\Invoice;
use App\Entity\Payment\PaymentMethod;
use App\Entity\User\User;
use App\Repository\AppointmentRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ClientRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\UserRepository;
use App\Service\AppointmentService\AppointmentService;
use App\Service\ConfigService\ConfigService;
use App\Service\DocumentService\DocumentService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Utils\PdfCreator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
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
use ZipArchive;

class InvoiceService extends AbstractService
{


    /**
     * @var InvoiceRepository
     */
    private InvoiceRepository $invoiceRepository;

    /**
     * @var PaymentMethodRepository
     */
    private PaymentMethodRepository $paymentMethodRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var ClientRepository
     */
    private ClientRepository $clientRepository;


    public function __construct(
        private readonly DocumentService $documentService,
        private readonly AppointmentService $appointmentService,
        private readonly ConfigService $configService,
        private readonly PdfCreator $pdfCreator,

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
        $this->invoiceRepository       = $em->getRepository(Invoice::class);
        $this->paymentMethodRepository = $em->getRepository(PaymentMethod::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->clientRepository = $em->getRepository(Client::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->invoiceRepository
        );
    }

    public function findServices(): Response
    {
        if(
            !$this->filterService->getFilterValue('entity') ||
            (
                $this->filterService->getFilterValue('entity') == User::ENTITY &&
                !$this->getUser()->isAdmin()
            )
        ){
            $this->filterService->addFilter('entity', Client::ENTITY);
        }

        $invoices      = $this->invoiceRepository->findInvoices($this->filterService);
        $users      = $this->userRepository->findBy(['autonomous' => true]);
        $clients      = $this->clientRepository->findAll();

        return $this->render('invoice/index.html.twig', [
            'totalResults'  => $invoices['totalRegisters'],
            'lastPage'      => $invoices['lastPage'],
            'currentPage'   => $this->filterService->page,
            'invoices'      => $invoices,
            'users'      => $users,
            'clients'      => $clients,
            'filterService' => $this->filterService
        ]);
    }

    public function generateInvoice(?string $appointment)
    {
        $appointment = $appointment ? $this->appointmentService->find($appointment) : null;
        $appointments = [];
        if($appointment){
            $appointments[] = $appointment;
        }else{
            if($this->getRequestPostParam('appointment_ids')){
                $appointments = $this->appointmentService->findAppointmentsByIds($this->getRequestPostParam('appointment_ids'));
            }
        }


        foreach ($appointments as $appointment) {
            $this->createClientInvoice($appointment);

        }

        $response = new JsonResponse(['SUCCESS' => true]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function generateUserInvoiceMultiple()
    {

        if ($this->isCsrfTokenValid('generate_multiple',  @$this->getRequestPostParam('_token'))){
            $configs = $this->configService->findConfigsFormatted();

            if (!$this->getUser()->isAdmin()) {
                $this->filterService->addFilter('user', [$this->getUser()->getId()]);
            }
            $this->filterService->setLimit(100000000000000);
            $appointments = $this->appointmentService->findAppointments($this->filterService, true)['data'];

            $schema = [];
            foreach ($appointments as $appointment) {
                $userId = $appointment->getUser()->getId();
                if ($appointment->getUser()->isAutonomous()) {

                    if (!@$schema[$userId]) {
                        $schema[$userId] = [
                            'user' => $appointment->getUser(),
                            'amountWithoutIva' => 0,
                            'amountWithIva' => 0,
                            'breakdown' => []
                        ];
                    }

                    $schema[$userId]['amountWithoutIva'] += ($appointment->getTotalWithoutIva() * $appointment->getUser()->getAppointmentPercentage()) / 100;
                    $schema[$userId]['amountWithIva']    += ($appointment->getTotalWithIva() * $appointment->getUser()->getAppointmentPercentage()) / 100;

                    foreach ($appointment->getServiceUserBreakdown() as $serviceName => $service){

                        if(!@$schema[$userId]['breakdown'][$serviceName]){
                            $schema[$userId]['breakdown'][$serviceName] = [
                                'service' => $serviceName,
                                'priceWithoutIva' => 0,
                                'priceWithIva' => 0,
                                'iva' => 0
                            ];
                        }

                        $schema[$userId]['breakdown'][$serviceName]['priceWithoutIva'] += $service['priceWithoutIva'];
                        $schema[$userId]['breakdown'][$serviceName]['priceWithIva'] += $service['priceWithIva'];
                        $schema[$userId]['breakdown'][$serviceName]['iva'] = $service['iva'];
                    }

                }

            }

            foreach ($schema as $userSchema) {

                $nextInvoiceNumber = $this->invoiceRepository->count(['serie' => @$configs[ConfigType::BILLING_USER_SERIE_TAG]]) + 1;
                $invoiceNumber     = @$configs[ConfigType::BILLING_USER_SERIE_TAG] . '/' . $nextInvoiceNumber;

                $this->invoiceRepository->createInvoice(
                    $invoiceNumber,
                    null,
                    null,
                    $userSchema['user'],
                    @$configs[ConfigType::BILLING_USER_SERIE_TAG],
                    $nextInvoiceNumber,
                    $userSchema['amountWithoutIva'],
                    $userSchema['amountWithIva'],
                    UTCDateTime::create(),
                    $configs[ConfigType::SHORT_COMPANY_NAME_TAG],
                    $configs[ConfigType::BILLING_PHONE_TAG],
                    $this->translate('Other'),
                    $configs[ConfigType::BILLING_ADDRESS_TAG],
                    $configs[ConfigType::BILLING_FIC_TAG],
                    $userSchema['user']->getFullName(),
                    $userSchema['user']->getNif(),
                    null,
                    $userSchema['user']->getAddress(),
                    $userSchema['breakdown']
                );

            }
        }


        $response = new JsonResponse(['SUCCESS' => true]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function renderEdit(string $invoice): Response
    {
        $invoice = $this->getEntity($invoice);

        return $this->render('invoice/edit.html.twig', [
            'invoice'        => $invoice,
            'paymentMethods' => $this->paymentMethodRepository->findAll()
        ]);
    }

    public function renderShow(string $invoice): Response
    {
        $invoice = $this->getEntity($invoice);

        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice
        ]);
    }

    public function processEdit(string $invoice): RedirectResponse
    {
        $invoice = $this->getEntity($invoice);

        if ($this->isCsrfTokenValid('edit-invoice', @$this->getRequestPostParam('_token'))) {
            $breakdown = [];
            if ($this->getRequestPostParam('breakdown')) {
                foreach ($this->getRequestPostParam('breakdown') as $serviceBreakdown) {
                    $breakdown[] = [
                        'service'         => $serviceBreakdown['service'],
                        'priceWithoutIva' => floatval($serviceBreakdown['priceWithoutIva']),
                        'iva'             => floatval($serviceBreakdown['iva']),
                        'priceWithIva'    => floatval($serviceBreakdown['priceWithIva'])
                    ];
                }

            }

            $invoiceNumber = $invoice->getSerie() . '/' . $this->getRequestPostParam('position');

            $exist = $this->invoiceRepository->findOneBy(['invoice_number' => $invoiceNumber]);

            if (!$exist || $exist->getId() == $invoice->getId()) {

                $this->invoiceRepository->updateInvoice(
                    $invoice,
                    $invoiceNumber,
                    $invoice->getAppointment(),
                    $invoice->getClient(),
                    $invoice->getUser(),
                    $invoice->getSerie(),
                    $this->getRequestPostParam('position'),
                    UTCDateTime::create('Y-m-d', $this->getRequestPostParam('invoice_date')),
                    $this->getRequestPostParam('social_reason'),
                    $this->getRequestPostParam('company_phone'),
                    $this->getRequestPostParam('payment_method'),
                    $this->getRequestPostParam('billing_address'),
                    $this->getRequestPostParam('cif'),
                    $this->getRequestPostParam('client'),
                    $this->getRequestPostParam('client_dni'),
                    $this->getRequestPostParam('client_phone'),
                    $this->getRequestPostParam('client_address'),
                    $breakdown
                );

                $this->addFlash('SUCCESS', $this->translate('The invoice has been successfully edited'));
            } else {
                $this->addFlash('ERROR', $this->translate('Could not be edited, invoice number already exists'));
            }

        }

        return $this->redirectToRoute('invoice_show', ['invoice' => $invoice->getId()]);
    }

    public function generateInvoicePdf(): bool
    {
        if ($this->isCsrfTokenValid('generate-pdf-zip', @$this->getRequestPostParam('_token'))) {

            if ($this->getRequestPostParam('all')) {

                $filters = [];
                parse_str($this->getRequestPostParam('filter_form'), $filters);


                foreach ($filters['filter_filters'] as $index => $filter) {
                    $this->filterService->addFilter($index, $filter);
                }

                $invoices = $this->invoiceRepository->findInvoices($this->filterService, true)['data'];

            } else {
                $invoicesArray = $this->getRequestPostParam('invoices');

                $invoices = $this->invoiceRepository->findByIds($invoicesArray);

            }

            $zipname = $this->translate('Invoices') . '.zip';
            $zip     = new ZipArchive;
            $zip->open($zipname, ZipArchive::CREATE);

            foreach ($invoices as $invoice) {
                $pdfName = $invoice->getInvoiceNumber() . '.pdf';
                $pdf     = $this->newPdf($invoice);
                $zip->addFromString($pdfName, $pdf);
            }

            $zip->close();

            $endDateCookie = UTCDateTime::create('now');
            $endDateCookie->modify('+3 hours');
            $expireformat = $endDateCookie->format('D, d M Y H:i:s e');

            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename=' . $zipname);
            header('Content-Length: ' . filesize($zipname));
            header ("Set-Cookie: downloaded=true; expires=$expireformat;path=/;");
            readfile($zipname);
            unlink($zipname);
        }

        return true;
    }

    public function createClientInvoice(
        Appointment $appointment
    )
    {
        $configs     = $this->configService->findConfigsFormatted();
        $nextInvoiceNumber = $this->invoiceRepository->count(['serie' => @$configs[ConfigType::BILLING_SERIE_TAG]]) + 1;
        $invoiceNumber     = @$configs[ConfigType::BILLING_SERIE_TAG] . '/' . $nextInvoiceNumber;
        $clientName        = $appointment->getClient()->getName() . ' ' . $appointment->getClient()->getSurnames();
        $exist             = $this->invoiceRepository->findBy(['invoiceDate' => $appointment->getTimeTo(), 'client' => $appointment->getClient(),  'appointment' => $appointment, 'dni' => $appointment->getClient()->getDni()]);


        if (!$exist) {
            $this->invoiceRepository->createInvoice(
                $invoiceNumber,
                $appointment,
                $appointment->getClient(),
                null,
                @$configs[ConfigType::BILLING_SERIE_TAG],
                $nextInvoiceNumber,
                $appointment->getTotalWithoutIva(),
                $appointment->getTotalWithIva(),
                $appointment->getTimeTo(),
                $configs[ConfigType::SHORT_COMPANY_NAME_TAG],
                $configs[ConfigType::BILLING_PHONE_TAG],
                $appointment->getPayments() ? $appointment->getPaymentMethods() : $this->translate('Other'),
                $configs[ConfigType::BILLING_ADDRESS_TAG],
                $configs[ConfigType::BILLING_FIC_TAG],
                $clientName,
                $appointment->getClient()->getDni(),
                $appointment->getClient()->getPhone1() ? $appointment->getClient()->getPhone1() : $appointment->getClient()->getPhone2(),
                $appointment->getClient()->getAddress(),
                $appointment->getServiceBreakdown()
            );

        }


    }

    public function createUserInvoice(
        Appointment $appointment
    )
    {
        $configs     = $this->configService->findConfigsFormatted();
        $nextInvoiceNumber = $this->invoiceRepository->count(['serie' => @$configs[ConfigType::BILLING_USER_SERIE_TAG]]) + 1;
        $invoiceNumber     = @$configs[ConfigType::BILLING_USER_SERIE_TAG] . '/' . $nextInvoiceNumber;
        $userName        = $appointment->getUser()->getName() . ' ' . $appointment->getUser()->getSurnames();
        $exist             = $this->invoiceRepository->findBy(['invoiceDate' => $appointment->getTimeTo(), 'user' => $appointment->getUser(), 'appointment' => $appointment, 'dni' => $appointment->getUser()->getNif()]);


        if (!$exist && $appointment->getUser()->isAutonomous()) {
            $this->invoiceRepository->createInvoice(
                $invoiceNumber,
                $appointment,
                null,
                $appointment->getUser(),
                @$configs[ConfigType::BILLING_USER_SERIE_TAG],
                $nextInvoiceNumber,
                ($appointment->getTotalWithoutIva() * $appointment->getUser()->getAppointmentPercentage())/100,
                ($appointment->getTotalWithIva() * $appointment->getUser()->getAppointmentPercentage())/100,
                $appointment->getTimeTo(),
                $configs[ConfigType::SHORT_COMPANY_NAME_TAG],
                $configs[ConfigType::BILLING_PHONE_TAG],
                $this->translate('Other'),
                $configs[ConfigType::BILLING_ADDRESS_TAG],
                $configs[ConfigType::BILLING_FIC_TAG],
                $userName,
                $appointment->getUser()->getNif(),
                null,
                $appointment->getUser()->getAddress(),
                $appointment->getServiceUserBreakdown()
            );

        }


    }


    public function deleteInvoice(string $invoice): RedirectResponse
    {
        $invoice = $this->getEntity($invoice);

        if ($this->isCsrfTokenValid('delete' . $invoice->getId(), $this->getRequestPostParam('_token'))) {
            $this->invoiceRepository->deleteInvoice($invoice);
        }

        $this->addFlash(
            'info',
            $this->translate('The invoice has been removed')
        );

        return $this->redirectBack();
    }

    public function exportPdf(string $invoice): Response
    {
        $invoice = $this->getEntity($invoice);

        $pdfName = $invoice->getInvoiceNumber() . '.pdf';
        $pdf     = $this->newPdf($invoice);

        return new Response(
            $pdf,
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'filename="' . $pdfName . '"'
            )
        );
    }

    public function newPdf(Invoice $invoice): bool|string
    {
        $this->pdfCreator->setDisplayMode('fullpage');
        $this->pdfCreator->resetInstance([
            'orientation' => 'P',
            'format' => 'A4',
            'mode' => 'utf-8',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 40,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10,
            'tempDir' => PdfCreator::TEMP_DIR,
        ]);

        $css = $this->documentService->getContentOfPublicAssetByUrl('assets/css/vendors/bootstrap.css');
        $this->pdfCreator->addCss($css);


        $this->pdfCreator->setFooter($this->renderView('pdf_templates/invoice/footer.html.twig', [
            'invoice' => $invoice,
        ]));

        $this->pdfCreator->setHeader($this->renderView('pdf_templates/invoice/header.html.twig', [
            'invoice' => $invoice,
        ]));

        $this->pdfCreator->addHtml($this->renderView(
            'pdf_templates/invoice/invoice.html.twig', array(
                'invoice' => $invoice
            )
        ));


        $fileRandomNumber = uniqid();

        $this->pdfCreator->getPdfOutput($fileRandomNumber, 'F');



        $pdf = file_get_contents($fileRandomNumber);
        unlink($fileRandomNumber);

        return $pdf;
    }

}