<?php


namespace App\Service\AppointmentService;




use App\Entity\Area\Area;
use App\Entity\Center\Center;
use App\Entity\Client\ClientHasDocument;
use App\Entity\Document\Document;
use App\Entity\User\UserHasDocument;
use App\Repository\AreaRepository;
use App\Repository\CenterRepository;
use App\Repository\ClientHasDocumentRepository;
use App\Repository\DocumentRepository;
use App\Repository\UserHasDocumentRepository;
use DateTime;
use DateTimeZone;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use App\Entity\Role\Role;
use App\Entity\User\User;
use App\Service\MailService;
use App\Entity\Client\Client;
use App\Entity\Status\Status;
use App\Service\FilterService;
use App\Entity\Payment\Payment;
use App\Entity\Service\Service;
use App\Entity\Service\Division;
use App\Shared\Utils\PdfCreator;
use App\Entity\Config\ConfigType;
use App\Entity\Template\Template;
use App\Entity\User\UserHasClient;
use App\Repository\UserRepository;
use App\Entity\Schedules\Schedules;
use App\Service\ExcelExportService;
use App\Service\MessageBirdService;
use App\Shared\Classes\UTCDateTime;
use App\Repository\ClientRepository;
use App\Repository\StatusRepository;
use App\Repository\PaymentRepository;
use App\Repository\ServiceRepository;
use App\Repository\DivisionRepository;
use App\Repository\TemplateRepository;
use App\Entity\Appointment\Appointment;
use App\Repository\SchedulesRepository;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AppointmentRepository;
use App\Entity\Appointment\AppointmentLog;
use App\Repository\UserHasClientRepository;
use App\Repository\AppointmentLogRepository;
use App\Service\ConfigService\ConfigService;
use App\Service\StripeService\StripeService;
use App\Service\MeetingService\MeetingService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use App\Service\DocumentService\DocumentService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\TemplateService\TemplateTypeService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\NotificationService\NotificationService;
use App\Entity\ExtraAppointmentField\ExtraAppointmentField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use App\Entity\ExtraAppointmentField\ExtraAppointmentFieldType;
use App\Service\ExtraAppointmentFieldTypeService\ExtraAppointmentFieldTypeService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AppointmentService extends AbstractService
{
    const UPLOAD_FILES_PATH = 'appointments';

    /**
     * @var AppointmentRepository
     */
    private AppointmentRepository $appointmentRepository;

    /**
     * @var StatusRepository
     */
    private StatusRepository $statusRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var AreaRepository
     */
    private AreaRepository $areaRepository;

    /**
     * @var UserHasClientRepository
     */
    private UserHasClientRepository $userHasClientRepository;

    /**
     * @var ClientRepository
     */
    private ClientRepository $clientRepository;
    /**
     * @var ServiceRepository
     */
    private ServiceRepository $serviceRepository;
    /**
     * @var SchedulesRepository
     */
    private SchedulesRepository $schedulesRepository;

    /**
     * @var CenterRepository
     */
    private CenterRepository $centerRepository;

    /**
     * @var PaymentRepository
     */
    private PaymentRepository $paymentRepository;

    /**
     * @var DivisionRepository
     */
    private DivisionRepository $divisionRepository;
    /**
     * @var AppointmentLogRepository
     */
    private AppointmentLogRepository $appointmentLogRepository;

    /**
     * @var TemplateRepository
     */
    private TemplateRepository $templateRepository;

    /**
     * @var DocumentRepository
     */
    private ClientHasDocumentRepository $clientHasDocumentRepository;

    public function __construct(
        private readonly TemplateTypeService              $templateTypeService,
        private readonly ExtraAppointmentFieldTypeService $extraAppointmentFieldTypeService,
        private readonly NotificationService              $notificationService,
        private readonly UrlGeneratorInterface            $urlGenerator,
        private readonly DocumentService                  $documentService,
        private readonly ConfigService                    $configService,
        private readonly MessageBirdService               $messageBirdService,
        private readonly MailService                      $mailService,
        private readonly StripeService                    $stripeService,
        private readonly PdfCreator                       $pdfCreator,
        private readonly MeetingService                   $meetingService,

        EntityManagerInterface                            $em,

        RouterInterface                                   $router,
        Environment                                       $twig,
        RequestStack                                      $requestStack,
        TokenStorageInterface                             $tokenStorage,
        CsrfTokenManagerInterface                         $tokenManager,
        FormFactoryInterface                              $formFactory,
        SerializerInterface                               $serializer,
        TranslatorInterface $translator,
        protected KernelInterface $kernel
    )
    {
        $this->appointmentRepository = $em->getRepository(Appointment::class);
        $this->statusRepository = $em->getRepository(Status::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->appointmentLogRepository = $em->getRepository(AppointmentLog::class);
        $this->templateRepository = $em->getRepository(Template::class);
        $this->clientRepository = $em->getRepository(Client::class);
        $this->paymentRepository = $em->getRepository(Payment::class);
        $this->serviceRepository = $em->getRepository(Service::class);
        $this->areaRepository = $em->getRepository(Area::class);
        $this->centerRepository = $em->getRepository(Center::class);
        $this->schedulesRepository = $em->getRepository(Schedules::class);
        $this->userHasClientRepository = $em->getRepository(UserHasClient::class);
        $this->divisionRepository = $em->getRepository(Division::class);
        $this->clientHasDocumentRepository = $em->getRepository(ClientHasDocument::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->appointmentRepository
        );

    }

    CONST MESES = array(1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre');

    public function find(string $appointmentId)
    {
        return $this->appointmentRepository->find($appointmentId);
    }

    public function findAppointmentById(string $appointmentId) :?Appointment
    {
        return $this->appointmentRepository->findAppointmentById($appointmentId);
    }

    public function findAppointmentsByIds(array $ids){
        return $this->appointmentRepository->findAppointmentsByIds($ids);
    }

    public function removeServices(Appointment $appointment):Appointment
    {
        return $this->appointmentRepository->removeServices($appointment);
    }


    public function findAppointments(FilterService $filterService, ?bool $showAll = false): array
    {
        return $this->appointmentRepository->findAppointments($filterService, $showAll);
    }

    public function checkIfIsCompleted(string $schedules, string $date, DateTime $time_from, DateTime $time_to, ?bool $fixed = false): bool
    {
        return $this->appointmentRepository->checkIfIsCompleted($schedules, $date, $time_from, $time_to, $fixed);
    }

    public function getOneAppointmentByDatesAndSchedule(DateTime $start, Datetime $end, Schedules $schedule): ?Appointment
    {
        return $this->appointmentRepository->getOneAppointmentByDatesAndSchedule($start,  $end, $schedule);
    }

    public function notifyAppointments( ?string $appointment): JsonResponse
    {
        $appointment = $this->getEntity($appointment, false);

        $appointments = [];
        if($appointment){
            $appointments[] = $appointment;
        }else{
            if($this->getRequestPostParam('appointment_ids')){
                $appointments = $this->appointmentRepository->findAppointmentsByIds($this->getRequestPostParam('appointment_ids'));
            }
        }

        $error = [];
        /** @var Appointment $appointment */
        foreach ($appointments as $appointment){
            $title = $title = $this->translate('Dear',$appointment->getClient()->getLocale()).' '.$appointment->getClient()->getName();
            $content = "<p>" .$this->translate('This is an informative email to remind you that you have an appointment with us, the data are reflected below' , $appointment->getClient()->getLocale()) . "</p>";
            $content .= "<p>";
            $content .= $this->translate('Date', $appointment->getClient()->getLocale()) . ": <b>" . $appointment->getTimeFrom()->setTimezone($appointment->getClient()->getTimezoneObj())->format('Y-m-d') . " " . $appointment->getTimeFrom()->setTimezone($appointment->getClient()->getTimezoneObj())->format('H:i') . "-" . $appointment->getTimeTo()->setTimezone($appointment->getClient()->getTimezoneObj())->format('H:i') . "</b>";
            if($appointment->isMeetingAttached() && $appointment->getMeeting()){
                $content .= "<br>";
                $content .= $this->translate('Meeting', $appointment->getClient()->getLocale()) . ": <b><a style='color: #E66100' href='" . $appointment->getMeeting()->getJoinUrl() . "'>" . $this->translate('Go to the Meeting', $appointment->getClient()->getLocale()) . "</a></b>";
            }
            $content .= "</p>";
            $mailResponse = $this->mailService->sendEmail($appointment, $this->translate('Appointment reminder', $appointment->getClient()->getLocale()), $title, $content);
            if(!$mailResponse){
                $errMessage = $appointment->getClient()->getName().' '.$appointment->getClient()->getSurnames().': ' . $this->translate('The email is not valid');
                if(!in_array($errMessage, $error)){
                    $error[] = $errMessage;
                }

            }
            $smsResponse = $this->messageBirdService->sendSMS($appointment, $this->translate('We remind you that you have an appointment with the following date', $appointment->getClient()->getLocale()) . ': ' . $appointment->getTimeFrom()->format('d-m-Y').', '.$appointment->getTimeFrom()->format('H:i').' - '.$appointment->getTimeTo()->format('H:i'));
            if(!$smsResponse){
                $errMessage = $appointment->getClient()->getName().' '.$appointment->getClient()->getSurnames().': ' . $this->translate('The phone is not valid');
                if(!in_array($errMessage, $error)){
                    $error[] = $errMessage;
                }

            }
        }

        $response = new JsonResponse(['errors' => $error]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function renderIframeForm(): Response
    {

        $services      = $this->serviceRepository->findBy(['active' => true]);

        return $this->render('appointment/iframe_form.html.twig', [
            'schedules' => $this->schedulesRepository->findByDays(),
            'extra_appointment_field_types' => $this->extraAppointmentFieldTypeService->findAllExtraAppointmentFieldTypes(),
            'services' => $services
        ]);

    }

    public function renderList()
    {
        $clientId = null;
        if($this->getCurrentRequest()->query->get('client_id') != null){
            $clientId = $this->getCurrentRequest()->query->get('client_id');
        }
        if($this->getUser()->isDirector()){
            $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
            $this->userRepository->findNonAdminUsers();
        }elseif ($this->getUser()->isAdmin()){
            $this->userRepository->findNonAdminUsers();
        }elseif($this->getUser()->isProject()){
            $projects = $this->userHasClientRepository->findBy(['user'=>$this->getUser()->getId()]);
            foreach($projects as $project){
                $projectId = [$project->getClient()->getId()];
            }
            $this->filterService->addFilter('client', $projectId);
        }else{
            $this->filterService->addFilter('user', $this->getUser()->getId());
            //$this->filterService->addFilter('statusType', 2);
        }

//        $servicesFilter = $this->serviceRepository->findBy(['forAdmin'=>false]);
        $this->filterService->addFilter('services', $this->serviceRepository->findBy(['forAdmin'=>false, 'forClient' => false]));

        $appointments = $this->appointmentRepository->findAppointments($this->filterService);

        $timeDif = [];

        foreach ($appointments['data'] as $appointment) {
            if ($appointment->getTimeFrom() != null and $appointment->getTimeTo() != null) {
                $dif = $appointment->getTimeFrom()->diff($appointment->getTimeTo());
                $timeDif[] = ['id' => $appointment->getId(), 'hours' => $dif->h, 'minutes' => $dif->i];
            }
        }

        $services = $this->serviceRepository->findBy(['active'=>true]);
        return $this->render('appointment/list.html.twig', [
            'totalResults' => $appointments['totalRegisters'],
            'lastPage' => $appointments['lastPage'],
            'totalAmount' => floatval($appointments['totalAmount']),
            'currentPage' => $this->filterService->page,
            'filterService' => $this->filterService,
            'appointments' => $appointments,
            'schedules' => $this->schedulesRepository->findByDays(),
            'areas'=> $this->areaRepository->findAll(),
            'centers'=> $this->centerRepository->findAll(),
            'clients' => $this->clientRepository->findBy(['status'=>'1']),
            'services' => $services,
            'clientId' => $clientId,
            'divisions' => $this->divisionRepository->findAll(),
            'allServices' => $this->serviceRepository->findBy(['active'=>true, 'forAdmin'=>false]),
            'status' => $this->statusRepository->findBy(['entityType' => Appointment::ENTITY]),
            'totalTime' => $timeDif
        ]);
    }

    public function processIframeForm(): RedirectResponse
    {

        if ($this->isCsrfTokenValid('new', $this->getRequestPostParam('_token'))) {
            $this->processCreateAppointmentRequest();
            $this->addFlash('success', $this->translate('The appointment has been successfully created'));
        }else{
            $this->addFlash('error', $this->translate('Invalid Token'));
        }
        return $this->redirectBack();

    }
    
    public function show( string $appointment){
        $appointment = $this->getEntity($appointment);

        $periodicity = false;
        $prev = 0;
        $next = 0;
        $dateReport= false;
        $actualTime = new DateTime();

        if($appointment->getPeriodicId() != null){
            $periodic = $this->appointmentRepository->count(['periodicId' => $appointment->getPeriodicId()]);
            if($periodic > 1){
                $periodicity = true;
                $datetime = UTCDateTime::create('Y-m-d', $appointment->getTimeFrom()->format('Y-m-d'), new \DateTimeZone('UTC'));
                $prev = sizeof($this->appointmentRepository->findByPeriodicIDAndDate($appointment->getPeriodicId(), $datetime, 'prev'));
                $next = sizeof($this->appointmentRepository->findByPeriodicIDAndDate($appointment->getPeriodicId(), $datetime, 'next'));
            }

        }

        if ($appointment->getTimeFrom()>$actualTime or $appointment->getTimeFrom()== null){

            $dateReport = true;
        }
//        dd($appointment->getClients());

        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
            'statuses' => $this->statusRepository->findBy(['entityType' => Appointment::ENTITY]),
            'templateTypes' => $this->templateTypeService->findBy(['entity' => Appointment::ENTITY, 'active' => true]),
            'periodic' => $periodicity,
            'user' => $this->getUser(),
            'prev' => $prev,
            'next' => $next,
            'dateReport' => $dateReport
        ]);
    }

    public function uploadAppointmentDocument($appointmentId,Request $request){
        $appointment = $this->appointmentRepository->find($appointmentId);
        $dateTime = $appointment->getTimeTo();
        $formattedDate = $dateTime->format("Ymd");
        if (!$appointment->getService()->isForAdmin() && !$appointment->getService()->isForClient()){ // Diagnostico Seguimiento
            $centerName = str_replace(' ', '', $appointment->getCenter()->getName());
            $clientName = str_replace(' ', '', $appointment->getClient()->getName());
            $userName   = str_replace(' ', '', $appointment->getUser()->getFullName());
            $filename = $formattedDate.'_'.$centerName.'_'.$clientName.'_'.$userName.'.pdf';
        }elseif($appointment->getService()->isForClient()) {//Grupales
            $centerName = str_replace(' ', '', $appointment->getCenter()->getName());
            $serviceName = str_replace(' ', '', $appointment->getService()->getName());
            $filename = $formattedDate.'_Acta'.$serviceName.'_'.$centerName.'.pdf';
        }else{
            $serviceName = str_replace(' ', '', $appointment->getService()->getName());
            $centerName = str_replace(' ', '', $appointment->getCenter()->getName());
            if($appointment->getClient() != null){
                $projectName = str_replace(' ', '', $appointment->getClient()->getName());
            }else{
                $projectName ='';
            }
            if ($appointment->getService()->getName() == 'Diagnóstico'){
                $filename = $formattedDate.'_Acta'.$serviceName.'_'.$centerName.'_'.$projectName.'.pdf';
            }else{
                $filename = $formattedDate.'_Acta'.$serviceName.'_'.$centerName.'.pdf';
            }
        }
        $document = $this->documentService->uploadRequest($filename);

        $documentType = $request->request->get('uploadDocument');
        if($documentType != "" || $documentType != null){
            $status = $this->statusRepository->find(Status::STATUS_COMPLETED);
            if ($documentType == 'reportSignMentor' && $appointment->getService()->isForAdmin()){
                $appointment->setStatusType($status);
            }elseif ($documentType == 'reportSignProject' && !$appointment->getService()->isForAdmin()){
                $appointment->setStatusType($status);
            }elseif($documentType == 'reportSignMentor' && !$appointment->getService()->isForAdmin() && !$appointment->getService()->isForClient()){
                $this->emailNotification($appointment);
            }
            $this->setEntityField($appointment, $documentType, $document);
            $this->appointmentRepository->persist($appointment);
        }
        return $this->redirectToRoute('appointment_show',['appointment' => $appointmentId]);
    }

    public function uploadAppointmentPhoto($appointmentId,Request $request){
        $appointment = $this->appointmentRepository->find($appointmentId);
        $document = $this->documentService->uploadRequest();
        $appointment->setPhoto($document);
        $this->appointmentRepository->persist($appointment);
        return $this->redirectToRoute('appointment_show',['appointment' => $appointmentId]);
    }

    public function setEntityField($entity, $fieldName, $fieldValue)
    {
        $setterName = 'set' . ucfirst($fieldName);

        if (method_exists($entity, $setterName)) {
            call_user_func([$entity, $setterName], $fieldValue);
        }
    }

    public function processCreateMultipleAppointmentsRequest(){
        $appointmentsData = $this->getRequestPostParam('appointments');
        $files = $this->getCurrentRequest()->files->get("appointments");

        $appointments = [];
        foreach ($appointmentsData as $index => $appointmentData){

            $extraAppointmentFields = @$appointmentData["extra_appointment_fields"] ?: [];
            $extraAppointmentFiles = @$files[$index]["extra_appointment_fields"] ?: [];

            $extraFields = $this->extraAppointmentFieldTypeService->format(null, $extraAppointmentFields, $extraAppointmentFiles, self::UPLOAD_FILES_PATH);

            $appointments[] = $this->createAppointment($appointmentData, $extraFields, null);

        }

        return $appointments;

    }

    public function processCreateAppointmentRequest(){
        $periodicId = uniqid();

        $appointmentData = $this->getRequestPostParam('appointment');
    
        $files = @$this->getCurrentRequest()->files->get("extra_appointment_fields") ?: [];
        $extraAppointmentFields = @$this->getRequestPostParam("extra_appointment_fields") ?: [];
        $extraFields = $this->extraAppointmentFieldTypeService->format(null, $extraAppointmentFields, $files, self::UPLOAD_FILES_PATH);

        if($this->getRequestPostParam('periodicCheck')){

            if($this->getRequestPostParam('periodicInterval')) {

                $appointment = $this->createAppointment($appointmentData, $extraFields, $periodicId);

                $errors = [];
                if($appointment){
                    $errors = $this->createPeriodicAppointments($appointment);
                }else{

                    return $this->redirectBack();
                }

                foreach ($errors as $error){
                    $this->addFlash(
                        'notice',
                        $error
                    );
                }
            }else{
                return $this->redirectBack();
            }
        }else{

            $appointment = $this->createAppointment($appointmentData, $extraFields, null);
            if(!$appointment){
                return $this->redirectBack();
            }
        }

    }

    public function new(): RedirectResponse|Response
    {
        if ($this->getCurrentRequest()->getMethod() == 'GET') {
            $divisions = $this->divisionRepository->findDivisionWithActiveServices();

            $servicesSelected = @$this->getRequestParam('services') ?: [];

            $clientSelected = @$this->getRequestParam('client') ?: [];

            $dateSelected = @$this->getRequestParam('appointment_date') ? $this->getRequestParam('appointment_date') : null;

            if ($this->getUser()->isProject()){
                $services = $this->serviceRepository->findBy(['active'=>false]);
            }else{
                $services = $this->serviceRepository->findAll();
            }

            $areas = $this->areaRepository->findAll();
            $extraAppointmentFieldTypes = [];
            foreach (ExtraAppointmentFieldType::POSITIONS as $value) {
                $extraAppointmentFieldTypes[$value] = $this->extraAppointmentFieldTypeService->findBy(['position' => $value]);
            }

            $this->filterService->addFilter('status', true);
                if ($this->getUser()->isDirector()){
                    $this->filterService->addFilter('center', $this->getUser()->getCenter());
                    $clients = $this->clientRepository->findClients($this->filterService,$this->getUser()->getId(),$this->getUser()->isAdmin(),true);
                    $center = [$this->centerRepository->find($this->getUser()->getCenter())];
                }else{
                    $clients = $this->clientRepository->findClients($this->filterService,$this->getUser()->getId(),$this->getUser()->isAdmin(),true);
                    $center = $this->centerRepository->findAll();
                }



            return $this->render('appointment/new.html.twig', [
                'user' => $this->getUser(),
                'areas' => $areas,
                'schedules' => $this->schedulesRepository->findByDays(),
                'extra_appointment_field_types' => $extraAppointmentFieldTypes,
                'clients' => $clients['data'],
                'divisions' => $divisions,
                'centers' => $center,
                'clientSelected' => $clientSelected,
                'services' => $services,
                'selectedServices' => $servicesSelected,
                'selectedDate' => $dateSelected
            ]);
        }


        if ($this->isCsrfTokenValid('new', $this->getRequestPostParam('_token'))) {
            $this->processCreateAppointmentRequest();

        }

        return $this->redirectToRoute('appointment_index');
    }

    public function index()
    {
        $users = [];

        if (!$this->getUser()->isAdmin()) {
            $this->filterService->addFilter('users', [$this->getUser()->getId()]);
            $this->filterService->addFilter('status', true);
        }else{
            $users = $this->userRepository->findAll();
        }

        if(@$_COOKIE['128Kalendar_date_from']){
            $from = $_COOKIE['128Kalendar_date_from'];
            $to = $_COOKIE['128Kalendar_date_to'];
        }else{
            $from = (UTCDateTime::create())->modify('first day of this month')->format('d-m-Y');
            $to = (UTCDateTime::create())->modify('last day of this month')->format('d-m-Y');
        }
        $divisions = $this->divisionRepository->findDivisionWithActiveServices();


        $this->filterService->addFilter('dateRange', $from . ' a ' . $to);
        $this->filterService->addFilter('date_from', null);
        $this->filterService->addFilter('date_to', null);

        $appointments = $this->appointmentRepository->findAppointments($this->filterService)['data'];
        

        $services = $this->serviceRepository->findBy(['active'=>true]);

        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointments,
            'schedules' => $this->schedulesRepository->findByDays(),
            'users' => $users,
            'clients' => $this->clientRepository->findBy(['status'=>'1']),
            'divisions' => $divisions,
            'filterService' => $this->filterService
        ]);
    }

    public function deleteAppointments(): RedirectResponse
    {

        if ($this->isCsrfTokenValid('delete-appointments', $this->getRequestPostParam('_token'))) {

            if ($this->getRequestPostParam('appointment_ids')) {

                $appointments = $this->appointmentRepository->findAppointmentsByIds($this->getRequestPostParam('appointment_ids'));
                //dd($appointments);
                foreach ($appointments as $appointment) {

                    $this->appointmentRepository->deleteAppointment($appointment);
                }
            }
        }

        if($this->getRequestPostParam('url') != null){
            return $this->redirect($this->getRequestPostParam('url'));
        }

        return $this->redirectToRoute('appointment_index');
    }

    public function formatAppointmentsToEvents(array $appointments, bool $showUser = false): array
    {
        $appointmentEvents = [];
        foreach ($appointments as $appointment) {
            $appointmentEvents[] = $appointment->toEvent($showUser);
        }
        return $appointmentEvents;
    }


    public function getEventsAppointmentsFromRequest(): array
    {

        $this->filterService->setLimit(5000);
        $showUser = false;

        if (!$this->getUser()->isAdmin()) {
            if ($this->getUser()->isProject()){
                $projects = $this->userHasClientRepository->findBy(['user'=>$this->getUser()->getId()]);
                foreach($projects as $project){
                    $projectId = [$project->getClient()->getId()];
                }
                $this->filterService->addFilter('client', $projectId);
            }else{
                $this->filterService->addFilter('users', [$this->getUser()->getId()]);
            }
        }else{
            if ($this->getUser()->isDirector()){
                $this->filterService->addFilter('center', $this->getUser()->getCenter()->getId());
            }else{
                $eventTitle = $this->filterService->getFilterValue('event_title');

                if($eventTitle == 'user'){
                    $showUser = true;

                }
            }

        }

        $this->filterService->addFilter('dateRange', $this->filterService->getFilterValue('date_from') . ' a ' . $this->filterService->getFilterValue('date_to'));
        $this->filterService->addFilter('date_from', null);
        $this->filterService->addFilter('date_to', null);

        $appointments = $this->appointmentRepository->findAppointments($this->filterService)['data'];

        return [
            'success' => true,
            'message' => 'OK',
            'data'    => $this->formatAppointmentsToEvents($appointments, $showUser)
        ];
    }

    public function getEventsClientAppointmentsFromRequest(): array
    {

        $this->filterService->setLimit(5000);
        $showUser = false;

        $this->filterService->addFilter('clients', [$this->getUser()->getId()]);

        $this->filterService->addFilter('dateRange', $this->filterService->getFilterValue('date_from') . ' a ' . $this->filterService->getFilterValue('date_to'));
        $this->filterService->addFilter('date_from', null);
        $this->filterService->addFilter('date_to', null);
        
        $appointments = $this->appointmentRepository->findAppointments($this->filterService)['data'];
        return [
            'success' => true,
            'message' => 'OK',
            'data'    => $this->formatAppointmentsToEvents($appointments, $showUser)
        ];
    }



    public function createAppointment(array $appointmentData, array $extraAppointmentFields, ?string $periodicId): ?Appointment
    {
        $appointment = new Appointment();

        $appointment->setStatus(true);

        $rolUserId = $this->getUser()->getRoleIds()[0];

        if (!empty($appointmentData['client'])) {
            $client = $this->clientRepository->find($appointmentData['client']);
            $appointment->addClient($client);
        }
        if (!empty($appointmentData['clients'])) {
            foreach ($appointmentData['clients'] as $clientId) {
                $client = $this->clientRepository->find($clientId);
                if ($client) {
                    $appointment->addClient($client);
                }
            }
        }

        $appointment->setPeriodicId($periodicId);
        $appointment->setPaid(false);
        $user = null;
            if (!empty($appointmentData['user'])) {
                $user = $this->userRepository->find($appointmentData['user']);
                $appointment->addUser($user);
            }
            if (!empty($appointmentData['users'])) {
                foreach ($appointmentData['users'] as $userId) {
                    $user = $this->userRepository->find($userId);
                    if ($user) {
                        $appointment->addUser($user);
                    }
                }
            }

        if (!empty($appointmentData['area'])) {
            $area = $this->areaRepository->find($appointmentData['area']);
            $appointment->setArea($area);
        }

        if (!empty($appointmentData['modality'])) {
            $appointment->setModality($appointmentData['modality']);
        }

        if (!empty($appointmentData['center'])) {
            $center=$this->centerRepository->find($appointmentData['center']);
            $appointment->setCenter($center);
        }



        if($appointmentData['schedules'] != null || $appointmentData['schedules'] != '')  {
            $schedule = $this->schedulesRepository->find($appointmentData['schedules']);
            $appointment->setSchedule($schedule);

            $timeFrom = UTCDateTime::create('H:i', $appointmentData['time_from']);
            $timeTo = UTCDateTime::create('H:i', $appointmentData['time_to']);

            $timeFrom = UTCDateTime::create('d-m-Y', $appointmentData['appointmentDate'])->setTime($timeFrom->format('H'), $timeFrom->format('i'));
            $timeTo = UTCDateTime::create('d-m-Y', $appointmentData['appointmentDate'])->setTime($timeTo->format('H'), $timeTo->format('i'));

            $appointment->setTimeFrom($timeFrom);
            $appointment->setTimeTo($timeTo);
            $appointment->setEmailSent(false);
            $appointments = $this->appointmentRepository->getAppointmentsByDateAndUser($timeFrom, $timeTo, $appointment->getUser());
            if(sizeof($appointments) >= 1){
                $this->addFlash(
                    'notice',
                    $timeFrom->format('Y-m-d H:i') . ': ' . $this->translate('Could not create the appointment for the date because the stretch is complete')
                );
                return null;
            }
        }

        $totalPrice = 0;
        if(isset($appointmentData['services'])){
            $appointment->removeAllService();



            foreach ($appointmentData['services'] as $service){
                $actual = $this->serviceRepository->find($service);
                $totalPrice += floatval($actual->getPrice());
                $appointment->addService($actual);
            }

            $appointment->setTotalPrice($totalPrice);
        }

        $appointment->setMeetingAttached((bool)(@$appointmentData['meeting_attached']));

        $this->checkAppointment($appointment);
        if($rolUserId == Role::ROLE_JEFE_ESTUDIOS || $rolUserId == Role::ROLE_DIRECTOR || $rolUserId == Role::ROLE_MENTOR){
            $appointment->setStatusType($this->statusRepository->find(Status::STATUS_ASSIGNED));
            if ($user and !empty($appointmentData['client'])){
                $userHasClient = $client->getUsersByClient();

                if (!in_array($user, $userHasClient)) {
                    $client->addUser($user);
                    $this->clientRepository->persist($client);
                }
            }
        }else{//ROLE_PROJECT
            $appointment->setStatusType($this->statusRepository->find(Status::STATUS_REQUEST));
        }


        $this->appointmentRepository->persist($appointment);

        if(!$appointment->getService()->isForAdmin() && !$appointment->getService()->isForClient()){
            if($appointmentData['schedules'] != null || $appointmentData['schedules'] != '')  {
                $this->email($appointment);
                $this->emailMentor($appointment);
            }
        }

        return $appointment;
    }

    public function email(Appointment $appointment){
        $title = 'Estimado/a '.$appointment->getClient()->getName();
        $content = "<p>Se le ha concertado una mentoría con el mentor ".$appointment->getUser()->getFullName() ."  </p>";
        $content .= "<p><b>Día:</b> ".$appointment->getTimeFrom()->format('d-m-Y') ."  </p>";
        $content .= "<p><b>Hora:</b> ".$appointment->getTimeFrom()->format('H:i')." - ".$appointment->getTimeTo()->format('H:i')."</p>";
        $this->mailService->sendAppointmentPayment($appointment ,$appointment->getClient()->getEmail(),null,$title,$content);
    }

    public function emailMentor(Appointment $appointment){
        $title = 'Estimado/a '.$appointment->getUser()->getFullName();
        $content = "<p>Se le ha concertado una mentoría con el proyecto ".$appointment->getClient()->getName() ."  </p>";
        $content .= "<p><b>Día:</b> ".$appointment->getTimeFrom()->format('d-m-Y') ."  </p>";
        $content .= "<p><b>Hora:</b> ".$appointment->getTimeFrom()->format('H:i')." - ".$appointment->getTimeTo()->format('H:i')."</p>";
        $this->mailService->sendAppointmentPayment($appointment ,$appointment->getUser()->getEmail(),null,$title,$content);
    }

    public function emailNotification(Appointment $appointment){
        $title = 'Estimado/a '.$appointment->getClient()->getRepresentative();
        $content = "<p>Ya esta disponible para descargar y firmar el acta de la siguiente mentoría:</p>";
        $content .= "<p><b>Mentor:</b> ".$appointment->getUser()->getFullName() ."  </p>";
        $content .= "<p><b>Día:</b> ".$appointment->getTimeFrom()->format('d-m-Y') ."  </p>";
        $content .= "<p><b>Hora:</b> ".$appointment->getTimeFrom()->format('H:i')." - ".$appointment->getTimeTo()->format('H:i')."</p>";
        $content .= "<p>En nuestra plataforma: <a href='https://aof.studio128k.com/login'>AOF</a></p>";
        $this->mailService->sendAppointmentPayment($appointment ,$appointment->getClient()->getEmail(),null,$title,$content);
    }


    public function report(string $appointmentId, Request $request){
        $document = $request->query->get('document');
        $appointment= $this->appointmentRepository->find($appointmentId);
        $actualDate = $appointment->getTimeTo();
        $finalDate = $actualDate->format('d \d\e ') . self::MESES[$actualDate->format('n')] . $actualDate->format(' \d\e Y');
        $director = $this->userRepository->findDirector($appointment->getCenter()->getId());
        return $this->render('pdf_templates/appointment/report/template.html.twig', [
            'areas' => $this->areaRepository->findAll(),
            'appointment' => $appointment,
            'document' => $document,
            'date' => $finalDate,
            'timeFrom' => $appointment->getTimeFrom()->format('H:i'),
            'timeTo' => $appointment->getTimeTo()->format('H:i'),
            'director' => end($director),
            'users' => $appointment->getUsers(),
            'clients' => $appointment->getClients(),
            'user' => $this->getUser(),
        ]);
    }


    public function checkAppointment(Appointment $appointment): Appointment
    {
        if($this->configService->findConfigValueByTag(ConfigType::COMPLETE_ON_PAY_TAG))
        {
            if($appointment->getPaid()){
                $status = $this->statusRepository->find(Status::STATUS_COMPLETED);
                $this->appointmentRepository->changeStatusType($appointment, $status);
            }

        }

        return $appointment;
    }

    public function complete( string $appointment): RedirectResponse
    {
        $appointment = $this->getEntity($appointment);

        $this->appointmentRepository->changeStatusType(
            $appointment,
            $this->statusRepository->find(Status::STATUS_COMPLETED)
        );

        if($this->getRequestPostParam('url') != null){
            return $this->redirect($this->getRequestPostParam('url'));
        }

        if($this->getRequestPostParam('route') != null){
            return $this->redirect($this->getRequestPostParam('route'));
        }


        return $this->redirectToRoute('appointment_index');
    }


    public function edit(string $appointment): RedirectResponse|Response
    {
        $appointment = $this->getEntity($appointment);

        if ($this->getCurrentRequest()->getMethod() == 'GET') {
            
            if($appointment->getTimeFrom()!= null && $appointment->getTimeFrom() < UTCDateTime::create('now')){
                $this->addFlash('error', $this->translate('Cannot edit an appointment already made'));
                return $this->redirectBack();
            }

            $divisions = $this->divisionRepository->findDivisionWithActiveServices();

            if($this->getUser()->isAdmin()){
                $users = $this->userRepository->findMentorUsers();
            
            }else{
                $this->filterService->addFilter('user', $this->getUser()->getId());
                $users = [$this->getUser()];
            };

            if ($this->getUser() ->isDirector()){
                $this->filterService->addFilter('center', $this->getUser()->getCenter());
                $clients = $this->clientRepository->findClients($this->filterService,$this->getUser()->getId(),$this->getUser()->isAdmin());
            }else{
                $clients = $this->clientRepository->findClients($this->filterService,$this->getUser()->getId(),$this->getUser()->isAdmin(),true);
            }
            if ($this->getUser()->isProject()){
                $services = $this->serviceRepository->findBy(['active'=>false]);
            }else{
                $services = $this->serviceRepository->findBy(['forAdmin'=>false]);
            }
            $areas = $this->areaRepository->findAll();

            $extraAppointmentFieldTypes = [];
            foreach (ExtraAppointmentFieldType::POSITIONS as $value) {
                $extraAppointmentFieldTypes[$value] = $this->extraAppointmentFieldTypeService->findBy(['position' => $value]);
            }

            return $this->render('appointment/edit.html.twig', [
                'users' => $users,
                'appointment' => $appointment,
                'areas' => $areas,
                'schedules' => $this->schedulesRepository->findByDays(),
                'extra_appointment_field_types' => $extraAppointmentFieldTypes,
                'services' => $services,
                'clients' => $clients['data'],
                'divisions' => $divisions,
                'status' => $this->statusRepository->findBy(['entityType' => Appointment::ENTITY])
            ]);
        }

        if ($this->isCsrfTokenValid('edit', $this->getRequestPostParam('_token'))) {

            $appointmentData = $this->getRequestPostParam('appointment');
            $files = @$this->getCurrentRequest()->files->get("extra_appointment_fields") ?: [];
            $extraAppointmentFields = @$this->getRequestPostParam("extra_appointment_fields") ?: [];

            $extraFields = $this->extraAppointmentFieldTypeService->format($appointment, $extraAppointmentFields, $files, self::UPLOAD_FILES_PATH);

            $this->editAppointment($appointmentData, $extraFields);
        }

        // $this->notifyAppointments($appointment->getId());

        return $this->redirectToRoute('appointment_show', ['appointment' => $appointment->getId()]);
    }

    public function editAppointment(array $appointmentData, array $extraFields)
    {
        $appointment = $this->findAppointmentById($appointmentData['id']);

//        $appointment->setClient($this->clientRepository->find($appointmentData['client']));

        if($appointmentData['services']){
            $appointment = $this->appointmentRepository->removeServices($appointment);

            $services = $appointmentData['services'] ?: [];

            foreach ($services as $service){
                $actual = $this->serviceRepository->find($service);
                $appointment->addService($actual);
            }

        }

        if (!empty($appointmentData['user'])) {
            $appointment->removeAllUsers();
            $user = $this->userRepository->find($appointmentData['user']);
            $appointment->addUser($user);
        }
        if (!empty($appointmentData['users'])) {
            foreach ($appointmentData['users'] as $userId) {
                $user = $this->userRepository->find($userId);
                if ($user) {
                    $appointment->addUser($user);
                }
            }
        }

        if (!empty($appointmentData['client'])) {
            $client = $this->clientRepository->find($appointmentData['client']);
            $appointment->addClient($client);
        }
        if (!empty($appointmentData['clients'])) {
            foreach ($appointmentData['clients'] as $clientId) {
                $client = $this->clientRepository->find($clientId);
                if ($client) {
                    $appointment->addClient($client);
                }
            }
        }


        if (!empty($appointmentData['area'])) {
            $area = $this->areaRepository->find($appointmentData['area']);
            $appointment->setArea($area);
        }

        $appointment->calculateTotalPrice();
        if(@$appointmentData['schedules']){
            $schedule = $this->schedulesRepository->find($appointmentData['schedules']);
            $appointment->setSchedule($schedule);
            $appointment->addUser($schedule->getUser());
        }

        if(@$appointmentData['time_from'] && @$appointmentData['time_to']){
            $timeFrom = UTCDateTime::create('H:i', $appointmentData['time_from']);
            $timeTo = UTCDateTime::create('H:i', $appointmentData['time_to']);

            $timeFrom = UTCDateTime::create('d-m-Y', $appointmentData['appointmentDate'])->setTime($timeFrom->format('H'), $timeFrom->format('i'));
            $timeTo = UTCDateTime::create('d-m-Y', $appointmentData['appointmentDate'])->setTime($timeTo->format('H'), $timeTo->format('i'));
            $appointment->setTimeFrom($timeFrom);
            $appointment->setTimeTo($timeTo);

            $appointments = $this->appointmentRepository->getAppointmentsByDateAndUser($timeFrom, $timeTo, $appointment->getUser());

            if(sizeof($appointments) > 1 || (sizeof($appointments) == 1 && $appointments[0]->getId() != $appointment->getId())){
                $this->addFlash(
                    'notice',
                    $timeFrom->format('Y-m-d H:i') . ': ' . $this->translate('Could not create the appointment for the date because the stretch is complete')
                );
                return null;
            }
        }



        if($extraFields){
            $appointment->removeAllAppointmentExtraFields();

            foreach($extraFields as $index => $field){
                $newExtraField = (new ExtraAppointmentField())
                    ->setUser($this->getUser())
                    ->setTitle($field['title'])
                    ->setType($field['type'])
                    ->setValue($field['value'])
                ;

                $appointment->addExtraAppointmentField($newExtraField);
            }
        }

        $this->checkAppointment($appointment);

        $appointment->setMeetingAttached((bool)(@$appointmentData['meeting_attached']));

        $this->appointmentRepository->persist($appointment);

        if($appointment->isMeetingAttached() && !$appointment->getMeeting()){
            $this->meetingService->createMeeting($appointment);
        }

        $this->appointmentLogRepository->createAppointmentLog(
            $appointment,
            $this->getUser(),
            AppointmentLog::JOB_APPOINTMENT_CHANGED,
            'This appointment has been modified'
        );

        $this->notificationService->createNotification(
            $this->translate('An Appointment has been edited that you had assigned, to consult your appointment click on the following link'),
            $this->urlGenerator->generate('appointment_show', ['appointment' => $appointment->getId()]),
            $appointment->getUser()
        );


    }


    public function createPeriodicAppointments(Appointment $appointment): array
    {
        $limit = UTCDateTime::create('Y-m-d', $this->getRequestPostParam('periodicEnd'));
        $interval = $this->getRequestPostParam('periodicInterval');

        $appointmentCloned = clone $appointment;

        $appointmentCloned->setTimeFrom($appointmentCloned->getTimeFrom(false)->modify("+$interval weeks"));
        $appointmentCloned->setTimeTo($appointmentCloned->getTimeTo(false)->modify("+$interval weeks"));

        $errors = [];

        while(($appointmentCloned->getTimeFrom() < $limit) == true){

            $appointments = $this->appointmentRepository->findBy(
                [
                    'timeFrom' => $appointmentCloned->getTimeFrom(false),
                    'user' => $appointmentCloned->getUser(),
                    'status' => true
                ]
            );

            if(sizeof($appointments) > 1){
                $errors[] = $appointmentCloned->getTimeFrom()->format('Y-m-d H:i') . ': ' . $this->translate('Could not create the appointment for the date because the stretch is complete');
            }else{
                if($appointmentCloned->getTimeFrom() < $limit){
                    if($appointmentCloned->isMeetingAttached()){
                        $this->meetingService->createMeeting($appointmentCloned);
                    }

                    $appointmentCloned = $this->appointmentRepository->persist($appointmentCloned);
                }
            }

            $appointmentCloned = clone $appointmentCloned;

            $appointmentCloned->setTimeFrom($appointmentCloned->getTimeFrom(false)->modify("+$interval weeks"));
            $appointmentCloned->setTimeTo($appointmentCloned->getTimeTo(false)->modify("+$interval weeks"));
        }


        return $errors;
    }

    public function exportPdf(string $appointmentId,Request $request){

        if(!$this->getUser()->isAdmin()){
            $this->filterService->addFilter('user', [$this->getUser()->getId()]);
        }
        $this->filterService->addOrderValue('date', 'ASC');
        $this->filterService->setLimit(100000000000000);
        $assistantMentors = null;
        $startupSelected = null;
        $selectedPhases = null;
        $selectedAreas = null;
        $selectedBusinessType = null;
        $mvp = null;
        $partner = null;
        $comercialSend= null;
        if ($request->request->get('mentores') != null){
            $assistantMentors = str_replace(['[', ']', '"'], '', $request->request->get('mentores'));
        }
        if ($request->request->get('startups') != null){
            $startupSelected = str_replace(['[', ']', '"'], '', $request->request->get('startups'));
        }
        if ($request->request->get('phases') != null){
            $selectedPhases = json_decode($request->request->get('phases'));
        }
        if ($request->request->get('businessType') != null){
            $selectedBusinessType = json_decode($request->request->get('businessType'));
        }
        if ($request->request->get('selectedAreas') != null){
            $selectedAreas = json_decode($request->request->get('selectedAreas'));
        }
        if ($request->request->get('mvp') != null){
            $mvp = $request->request->get('mvp');
        }
        if ($request->request->get('comercialSend') != null){
            $comercialSend = $request->request->get('comercialSend');
        }
        if ($request->request->get('partner') != null){
            $partner = $request->request->get('partner');
        }
        $ceo = $request->request->has('ceo') ? $request->request->get('ceo') : null;
        $numberEmployee = $request->request->has('numberEmployee') ? $request->request->get('numberEmployee') : null;
        $numberIntern = $request->request->has('numberIntern') ? $request->request->get('numberIntern') : null;
        $investment = $request->request->has('investment') ? $request->request->get('investment') : null;
        $invoice = $request->request->has('invoice') ? $request->request->get('invoice') : null;

        $topic = $request->request->get('topic');
        $commitment = $request->request->get('commitment');
        $comment = $request->request->get('comment');
        $document = $request->request->get('document');

        $this->pdfCreator->setDisplayMode('fullpage');
        $this->pdfCreator->resetInstance([
            'orientation' => 'P',
            'format' => 'A4',
            'mode' => 'utf-8',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 8,
            'tempDir' => PdfCreator::TEMP_DIR,
        ]);
        $appointment= $this->appointmentRepository->find($appointmentId);
        $director = $this->userRepository->findDirector($appointment->getCenter()->getId());
        $actualDate = $appointment->getTimeTo();
        $finalDate = $actualDate->format('d \d\e ') . self::MESES[$actualDate->format('n')] . $actualDate->format(' \d\e Y');
        $this->pdfCreator->addCss(file_get_contents($this->kernel->getProjectDir().'/public/assets/css/vendors/bootstrap.css'));
        $this->pdfCreator->addHtml($this->renderView(
            'pdf_templates/appointment/report/template.html.twig', array(
                'areas' => $this->areaRepository->findAll(),
                'selectedAreas' => $selectedAreas,
                'phases' => $selectedPhases,
                'businessTypes' => $selectedBusinessType,
                'comercialSend' =>$comercialSend,
                'appointment' => $appointment,
                'mvp' => $mvp,
                'partner' => $partner,
                'invoice' => $invoice,
                'investment' => $investment,
                'numberIntern' => $numberIntern,
                'numberEmployee' =>$numberEmployee,
                'ceo' => $ceo,
                'document' => $document,
                'topic' => $topic,
                'commitment' => $commitment,
                'comment' => $comment,
                'user' => $this->getUser(),
                'users' => $appointment->getUsers(),
                'clients' => $appointment->getClients(),
                'pdf' => true,
                'director' => end($director),
                'date' => $finalDate,
                'mentores' => $assistantMentors,
                'startupSelected' => $startupSelected,
                'timeFrom' => $appointment->getTimeFrom()->format('H:i'),
                'timeTo' => $appointment->getTimeTo()->format('H:i'),

            )
        ));

        $fileName = $this->kernel->getProjectDir().'/resources/documents/appointments/tmp/'.$document.'.pdf';
        $this->pdfCreator->getPdfOutput($fileName, 'F');

        $fileToCreateDocument = new UploadedFile($fileName, $fileName,null,null, true);


        $documentObject = $this->documentService->uploadDocument($fileToCreateDocument, 'appointment');

        $appointment->setReport($documentObject);
        $this->appointmentRepository->persist($appointment);

        $this->addFlash('success','Se ha descargado el acta con exito');
        return $this->documentService->downloadDocument($documentObject->getId());
    }

    public function sortAppointments(array $appointments): array
    {
        $appointmentsSorted = array();

        /** @var Appointment $appointment */
        foreach($appointments as $appointment)
        {
            $date = $appointment->getTimeFrom(false)->format('Y-m-d');
            $userId = $appointment->getUser()->getId();
            if(!isset($appointmentsSorted[$date]))
            {
                $appointmentsSorted[$date] = array();
            }

            if(!isset($appointmentsSorted[$date][$userId]))
            {
                $appointmentsSorted[$date][$userId] = ['user' => $appointment->getUser(), 'schedules' => $appointment->getUser()->getSchedulesByWeekDay($appointment->getTimeFrom()->format('w')), 'appointments' => array()];
            }

            $appointmentsSorted[$date][$userId]['appointments'][$appointment->getSchedule()->getId()][] = $appointment;
        }

        return $appointmentsSorted;
    }

    public function checkPaidAppointment(Appointment $appointment): void
    {
        if($appointment->checkPaid()){
            $appointment->setPaid(true);
        }else{
            $appointment->setPaid(false);
        }

        $this->appointmentRepository->persist($appointment);
    }


    public function exportExcel(): Response
    {

        if(!$this->getUser()->isAdmin()){
            $this->filterService->addFilter('user', [$this->getUser()->getId()]);
        }
        $this->filterService->setLimit(100000000000000);
        $appointments = $this->appointmentRepository->findAppointments($this->filterService);

        $clientNomenclature = $this->configService->findConfigValueByTag(ConfigType::CLIENT_NOMENCLATURE_TAG);
        $userNomenclature = $this->configService->findConfigValueByTag(ConfigType::USER_NOMENCLATURE_TAG);

        $exportService = new ExcelExportService();

        $exportService->setName('citas');
        $exportService->setHeaders([
            $this->translate($clientNomenclature),
            $this->translate("$clientNomenclature email"),
            $this->translate($userNomenclature),
            $this->translate('Date'),
            $this->translate('Start Time'),
            $this->translate('End Time'),
            $this->translate('Services'),
            $this->translate('Total price'),
            $this->translate('Paid')
        ]);
        $data = [];

        foreach ($appointments['data'] as $appointment){
            $client = $appointment->getClient();
            $user = $appointment->getUser();
            $services = $appointment->getServices();
            $servicesStr = '';

            foreach ($services as $service){
                $servicesStr .= ''.$service->getName().', ';
            }
            $servicesStr = substr($servicesStr, 0, -2);


            $appointmentData = [
                $client->getName().' '.$client->getSurnames(),
                $client->getEmail(),
                $user->getName().' '.$user->getSurnames(),
                $appointment->getTimeFrom()->format('d-m-Y'),
                $appointment->getTimeFrom()->format('H:i'),
                $appointment->getTimeTo()->format('H:i'),
                $servicesStr,
                $appointment->getTotalPrice(),
                $appointment->getPaid() ? $this->translate('Yes') : $this->translate('No')
            ];

            $data[] = $appointmentData;
        }

        $exportService->setArrayData($data);

        $exportService->setMetadata(
            $this->translate("Appointment Report"),
            $this->translate("Appointments"),
            "",
            $this->translate("Appointment Report"),
            "",
            $this->translate("Appointment Report"),
        );

        $response = $exportService->exportArrayToExcel();

        $endDateCookie = UTCDateTime::create('now');
        $endDateCookie->modify('+3 hours');
        $expireformat = $endDateCookie->format('D, d M Y H:i:s e');

        return new Response($response, 200,
            array(
                'Content-Type'          => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition'   => 'attachment; filename="' . $this->translate("Appointments") . '.xlsx"',
                'Set-Cookie'            => "downloaded=true; expires=$expireformat;path=/;"
            ));
    }


    public function copyAppointments(?string $appointment): JsonResponse
    {
        $appointment = $this->getEntity($appointment, false);

        $appointments = [];
        if($appointment){
            $appointments[] = $appointment;
        }else{
            if($this->getRequestPostParam('appointment_ids')){
                $appointments = $this->appointmentRepository->findAppointmentsByIds($this->getRequestPostParam('appointment_ids'));
            }
        }

        $error = [];
        $newDate = UTCDateTime::create('Y-m-d', $this->getRequestPostParam('date'));

        $schedule = null;
        if(@$this->getRequestPostParam('schedule')){
            $schedule = $this->schedulesRepository->find($this->getRequestPostParam('schedule'));
        }

        foreach ($appointments as $appointment){
            $message = $this->copyWithNewDate($appointment, clone $newDate, $schedule);
            if($message){
                $error[] = $message;
            }
        }

        $response = new JsonResponse(['errors' => $error]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function copyWithNewDate(Appointment $appointment, DateTime $newDate, ?Schedules $schedule = null)
    {

        if(!$schedule){
            /** @var ?Schedules $schedule */
            $schedule = $this->schedulesRepository->findAvailableScheduleByUserAndDates($appointment->getUser(), $appointment->getTimeFrom(false), $appointment->getTimeTo(false), $newDate->format('w'));
        }

        if($schedule){
            $startDate = (clone $newDate)->setTime($schedule->getTimeFrom()->format('H'), $schedule->getTimeFrom()->format('i'));
            $endDate = (clone $newDate)->setTime($schedule->getTimeTo()->format('H'), $schedule->getTimeTo()->format('i'));

            $appointments = $this->appointmentRepository->getAppointmentsByDateAndUser($startDate, $endDate, $schedule->getUser());

            if(
                ($schedule->isFixed() && sizeof($appointments) == 0)
                || (!$schedule->isFixed() && sizeof($appointments) < 2)
            ){
                $this->appointmentRepository->createAppointment(
                    $appointment->getUser(),
                    $appointment->getClient(),
                    $schedule,
                    $appointment->getServices(),
                    true,
                    $startDate,
                    $endDate,
                    $this->statusRepository->find(2),
                    false,
                    $appointment->getPeriodicId()
                );


                $this->notificationService->createNotification(
                    $this->translate('You have been assigned a new Appointment, to consult your appointment click on the following link'),
                    $this->urlGenerator->generate('appointment_show', ['appointment' => $appointment->getId()]),
                    $appointment->getUser()
                );

            }else{
                return $appointment->getClient()->getFullName() .': ' . $this->translate('Could not be created, the agenda is complete');
            }
        }else{
            return $appointment->getClient()->getFullName() .': ' . $this->translate('Could not create, no valid agenda for the selected day');
        }

    }


    public function modifyHour(string $appointment): RedirectResponse
    {

        if ($this->isCsrfTokenValid('modify-hour', $this->getRequestPostParam('_token'))) {
            $appointment = $this->getEntity($appointment);

            $timeFrom = UTCDateTime::create('Y-m-d H:i', $appointment->getTimeFrom()->format('Y-m-d') . ' ' . $this->getRequestPostParam('from'));
            $timeTo = UTCDateTime::create('Y-m-d H:i', $appointment->getTimeTo()->format('Y-m-d') . ' ' . $this->getRequestPostParam('to'));

            if($timeFrom && $timeTo){
                $oldTime = $appointment->getTimeFrom()->format('H:i') . ' - ' . $appointment->getTimeTo()->format('H:i');
                $newTime = $timeFrom->format('H:i') . ' - ' . $timeTo->format('H:i');
                $appointment = $this->appointmentRepository->modifyHour($appointment, $timeFrom, $timeTo);
                $appointments = $this->appointmentRepository->getAppointmentsBetweenDatesAndUser($timeFrom, $timeTo, $appointment->getUser());

                $this->appointmentLogRepository->createAppointmentLog(
                    $appointment,
                    $this->getUser(),
                    AppointmentLog::JOB_TIME_CHANGED,
                    "The appointment time has had a modification, this change being as follows: $oldTime -> $newTime"
                );

                if(count($appointments) > 1){
                    $this->addFlash('error', $this->translate('Warning') . ': ' . $this->translate('There are appointments for this user that share time'));
                }

                $this->addFlash('success', $this->translate('Schedule successfully modified'));

            }

        }

        return $this->redirectBack();
    }

    public function changeStatus(string $appointmentId): Response
    {
        $appointment = $this->appointmentRepository->find($appointmentId);
        if ($this->isCsrfTokenValid('change-status', $this->getRequestPostParam('_token'))) {
            $status = $this->statusRepository->find($this->getRequestPostParam('status'));
            if($status->getId()==Status::STATUS_ASSIGNED){
                if ($appointment->getUser()){
                    $userHasClient = $appointment->getClient()->getUsersByClient();

                    if (!in_array($appointment->getUser(), $userHasClient)) {
                        $appointment->getClient()->addUser($appointment->getUser());
                        $this->clientRepository->persist($appointment->getClient());
                    }
                }

                $this->email($appointment);
                $this->emailMentor($appointment);
            }
            $appointment->setStatusType($status);

            $this->appointmentRepository->persist($appointment);


        }
        return $this->redirectToRoute('appointment_show', ['appointment' => $appointment->getId()]);
    }

    public function getAppointment(): JsonResponse
    {
        $appointment = $this->appointmentRepository->findById($this->getRequestPostParam('appointment_id'));
        $serviceAppointment = $this->appointmentRepository->findServices($this->getRequestPostParam('appointment_id'));

        if($serviceAppointment != null){
            $services = $serviceAppointment['services'];
        }else{
            $services = [];
        }

        $response = new JsonResponse(['appointment' => $appointment, 'services' => $services]);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function delete(string $appointment): RedirectResponse
    {
        if($this->isCsrfTokenValid('delete', $this->getRequestPostParam('_token'))){

            $appointment = $this->getEntity($appointment);

            $this->appointmentRepository->deleteAppointment($appointment);

            if($this->getRequestPostParam('route') != null){
                return $this->redirect($this->getRequestPostParam('route'));
            }
            if($this->getRequestPostParam('url') != null){
                return $this->redirect($this->getRequestPostParam('url'));
            }
        }

        return $this->redirectToRoute('appointment_index');
    }

    public function deletePeriodicId(string $appointment): Response
    {

        $appointment = $this->getEntity($appointment);

        $periodicId = $appointment->getPeriodicId();
        $datetime = UTCDateTime::create('Y-m-d', $appointment->getTimeFrom()->format('Y-m-d'));
        $appointments = $this->appointmentRepository->findByPeriodicIDAndDate($periodicId, $datetime, $this->getRequestPostParam('operator'));
        foreach($appointments as $loopAppointment){
            $this->appointmentRepository->deleteAppointment($loopAppointment);
        }
        if($this->getRequestPostParam('route') != null){
            return $this->redirect($this->getRequestPostParam('route'));
        }


        return $this->redirectToRoute('appointment_show', ['appointment' => $appointment->getId()]);
    }

}