<?php


namespace App\Service\TemplateService;


use App\Entity\Appointment\Appointment;
use App\Entity\Appointment\AppointmentLog;
use App\Entity\Client\Client;
use App\Entity\Config\Config;
use App\Entity\Document\Document;
use App\Entity\Template\Template;
use App\Entity\Template\TemplateLineType;
use App\Entity\Template\TemplateType;
use App\Entity\User\User;
use App\Repository\AppointmentLogRepository;
use App\Repository\AppointmentRepository;
use App\Repository\ClientRepository;
use App\Repository\ConfigRepository;
use App\Repository\TemplateRepository;
use App\Repository\TemplateTypeRepository;
use App\Repository\UserRepository;
use App\Service\DocumentService\DocumentService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Utils\PdfCreator;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TemplateService extends AbstractService
{

    const UPLOAD_FILES_PATH = 'templates';

    /**
     * @var TemplateRepository
     */
    private TemplateRepository $templateRepository;
    /**
     * @var ClientRepository
     */
    private ClientRepository $clientRepository;
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var TemplateTypeRepository
     */
    private TemplateTypeRepository $templateTypeRepository;
    /**
     * @var AppointmentRepository
     */
    private AppointmentRepository $appointmentRepository;

    /**
     * @var ConfigRepository
     */
    private ConfigRepository $configRepository;

    /**
     * @var AppointmentLogRepository
     */
    private AppointmentLogRepository $appointmentLogRepository;


    public function __construct(
        private readonly DocumentService $documentService,
        private PdfCreator $pdfCreator,

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

        $this->templateRepository = $em->getRepository(Template::class);
        $this->clientRepository = $em->getRepository(Client::class);
        $this->templateTypeRepository = $em->getRepository(TemplateType::class);
        $this->appointmentRepository = $em->getRepository(Appointment::class);
        $this->configRepository = $em->getRepository(Config::class);
        $this->appointmentLogRepository = $em->getRepository(AppointmentLog::class);
        $this->userRepository = $em->getRepository(User::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->templateRepository
        );
    }

    public function findAll(): array
    {
        return $this->templateRepository->findAll();
    }

    public function getCreateTemplateTemplate(): JsonResponse
    {
        if ($this->isCsrfTokenValid('get-create-template-template', $this->getRequestPostParam('_token'))) {

            $templateType = $this->templateTypeRepository->find($this->getRequestPostParam('templateType'));

            if($templateType){
                $template = $this->renderView('template/create_template.html.twig', [
                    'templateType' => $templateType,
                    'client' => $this->getRequestPostParam('client'),
                    'appointment' => @$this->getRequestPostParam('appointment'),
                    'event' => @$this->getRequestPostParam('event')
                ]);

                return new JsonResponse(['success' => true, 'data' => $template, 'message' => 'Plantilla de creación de plantillas']);
            }else{
                return new JsonResponse(['success' => false, 'message' => 'No se ha encontrado el tipo de plantilla']);
            }

        }
        return new JsonResponse(['success' => false, 'message' => 'El token no es válido']);
    }

    public function getEditTemplateTemplate(): JsonResponse
    {
        if ($this->isCsrfTokenValid('get-edit-template-template', $this->getRequestPostParam('_token'))) {

            $template = $this->templateRepository->find($this->getRequestPostParam('template'));

            if($template){
                $template = $this->renderView('template/edit_template.html.twig', [
                    'template' => $template
                ]);

                return new JsonResponse(['success' => true, 'data' => $template, 'message' => 'Plantilla de creación de entrada de historial']);
            }else{
                return new JsonResponse(['success' => false, 'message' => 'No se ha encontrado el cliente']);
            }

        }
        return new JsonResponse(['success' => false, 'message' => 'El token no es válido']);
    }

    public function editTemplatesByRequest(): RedirectResponse
    {
        if ($this->isCsrfTokenValid('edit-template', $this->getRequestPostParam('_token'))) {

            $template = $this->templateRepository->find($this->getRequestPostParam('id'));

            if ($template){
                $templateType = @$this->getRequestPostParam('template_type') ? $this->templateTypeRepository->find($this->getRequestPostParam('template_type')) : null;
                $client = @$this->getRequestPostParam('client') ? $this->clientRepository->find($this->getRequestPostParam('client')) : null;
                $appointment = @$this->getRequestPostParam('appointment') ? $this->appointmentRepository->find($this->getRequestPostParam('appointment')) : null;
                $templateLinesArray = $this->getRequestPostParam('template_lines');
                $files = @$this->getCurrentRequest()->files->get("template_lines")?: [];

                $templateLines = $this->formatLines($template, $templateLinesArray, $files, self::UPLOAD_FILES_PATH);

                $template = $this->templateRepository->editTemplate(
                    $template,
                    $templateType->getName(),
                    $templateType,
                    $client,
                    $appointment,
                    $this->getUser(),
                    $templateLines
                );
            }

            $this->addFlash('success', $this->translate('The template has been edited successfully'));
        }

        return $this->redirectBack();
    }

    public function createTemplateByRequest(): RedirectResponse
    {
        if ($this->isCsrfTokenValid('create-template', $this->getRequestPostParam('_token'))) {
            $templateType = @$this->getRequestPostParam('template_type') ? $this->templateTypeRepository->find($this->getRequestPostParam('template_type')) : null;
            $client = @$this->getRequestPostParam('client') ? $this->clientRepository->find($this->getRequestPostParam('client')) : null;
            $appointment = @$this->getRequestPostParam('appointment') ? $this->appointmentRepository->find($this->getRequestPostParam('appointment')) : null;
            $templateLinesArray = $this->getRequestPostParam('template_lines');
            $files = @$this->getCurrentRequest()->files->get("template_lines") ?: [];

            $templateLines = $this->formatLines(null, $templateLinesArray, $files, self::UPLOAD_FILES_PATH);

            $template = $this->templateRepository->createTemplate(
                $templateType->getName(),
                $templateType,
                $client,
                $appointment,
                $this->getUser(),
                $templateLines
            );

            $this->appointmentLogRepository->createAppointmentLog(
                $appointment,
                $this->getUser(),
                AppointmentLog::JOB_ADD_TEMPLATE,
                'A template of the following type has been created: "' . $templateType->getName() . '".'
            );

            $this->exportPdfAndAdd($template->getId());
            $this->addFlash('success', $this->translate('Cuestionario creado con éxito'));
        }

        return $this->redirectBack();
    }

    public function deleteTemplate(string $template): RedirectResponse
    {
        $template = $this->getEntity($template);

        if ($this->isCsrfTokenValid('delete', $this->getRequestPostParam('_token'))) {
            $this->templateRepository->remove($template);
        }

        return $this->redirectBack();
    }

    public function exportPdf(){
        $templates =[];
        $templateTypes =[];
        if(@$this->getRequestPostParam('template')) {
            $template =  $this->templateRepository->find($this->getRequestPostParam('template'));
            $templates[] = $template;
            $client = $template->getClient();
            $appointment = $template->getAppointment();
            $user = $template->getUser();
            $templateTypes[] = $template->getTemplateType();
        }else{

            $dates = explode(' a ', $this->getRequestPostParam('date_range'));
            if(sizeof($dates) > 1){
                $timeFrom = UTCDateTime::create('d-m-Y', $dates[0], new \DateTimeZone('UTC'))->setTime(0, 0);
                $timeTo   = UTCDateTime::create('d-m-Y', $dates[1], new \DateTimeZone('UTC'))->setTime(23, 59);

            }else{
                $timeFrom = UTCDateTime::create('d-m-Y', $dates[0], new \DateTimeZone('UTC'))->setTime(0, 0);
                $timeTo   = UTCDateTime::create('d-m-Y', $dates[0], new \DateTimeZone('UTC'))->setTime(23, 59);
            };

            $client = @$this->getRequestPostParam('client') ? $this->clientRepository->find($this->getRequestPostParam('client')) : null;
            $appointment = @$this->getRequestPostParam('appointment') ? $this->appointmentRepository->find($this->getRequestPostParam('appointment')) : null;
            $templateTypes = $this->templateTypeRepository->findTemplateTypesByIds($this->getRequestPostParam('types'));
            $user = @$this->getRequestPostParam('user') ? $this->userRepository->find($this->getRequestPostParam('user')) : null;

            $templates = $this->templateRepository->findAllInfoByTypes(
                @$this->getRequestPostParam('appointment'),
                @$this->getRequestPostParam('client'),
                @$this->getRequestPostParam('user'),
                @$this->getRequestPostParam('types'),
                $timeFrom,
                $timeTo
            );
        }


        $templatesSorted = $this->sortTemplates($templates);

        $this->pdfCreator->setDisplayMode('fullpage');
        $this->pdfCreator->resetInstance([
            'orientation' => 'P',
            'format' => 'A4',
            'mode' => 'utf-8',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 30,
            'margin_header' => 10,
            'margin_footer' => 10,
            'setAutoBottomMargin' => 'pad',
            'tempDir' => PdfCreator::TEMP_DIR,
        ]);

        $css = $this->documentService->getContentOfPublicAssetByUrl('assets/css/vendors/bootstrap.css');
        $this->pdfCreator->addCss($css);

        if($user){
            $startupName = $user->getClient()->getName();
        }else{
            $startupName = null;
        }

        $css = $this->documentService->getContentOfPublicAssetByUrl('assets/css/vendors/quill/quill.snow.css');
        $this->pdfCreator->addCss($css);

        $this->pdfCreator->setFooter($this->renderView('pdf_templates/template/footer.html.twig', [
        ]));

        $this->pdfCreator->setHeader($this->renderView('pdf_templates/template/header.html.twig', [
        ]));

        $this->pdfCreator->addHtml($this->renderView('pdf_templates/template/template.html.twig', array(
            'templates'  => $templatesSorted,
            'templateTypes'  => $templateTypes,
            'client' => $client,
            'appointment' => $appointment,
            'startup' =>$startupName
        )));

        $fileName = 'Exito.pdf';
        $this->pdfCreator->getPdfOutput($fileName, 'F');
        $pdf = file_get_contents($fileName);

        return new Response(
            $pdf,
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'filename=" ' . $fileName . ' "'
            )
        );

    }

    public function exportPdfAndAdd(string $templateId){

        $template =  $this->templateRepository->find($templateId);
        $templates[] = $template;
        $client = $template->getClient();
        $appointment = $template->getAppointment();
        $user = $template->getUser();
        $templateTypes[] = $template->getTemplateType();

        $templatesSorted = $this->sortTemplates($templates);

        $this->pdfCreator->setDisplayMode('fullpage');
        $this->pdfCreator->resetInstance([
            'orientation' => 'P',
            'format' => 'A4',
            'mode' => 'utf-8',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 30,
            'margin_header' => 10,
            'margin_footer' => 10,
            'setAutoBottomMargin' => 'pad',
            'tempDir' => PdfCreator::TEMP_DIR,
        ]);

        $css = $this->documentService->getContentOfPublicAssetByUrl('assets/css/vendors/bootstrap.css');
        $this->pdfCreator->addCss($css);


        $css = $this->documentService->getContentOfPublicAssetByUrl('assets/css/vendors/quill/quill.snow.css');
        $this->pdfCreator->addCss($css);

        $this->pdfCreator->setFooter($this->renderView('pdf_templates/template/footer.html.twig', [
        ]));

        $this->pdfCreator->setHeader($this->renderView('pdf_templates/template/header.html.twig', [
        ]));

        if($user){
            $startupName = $user->getClient()->getName();
        }else{
            $startupName = null;
        }

        $this->pdfCreator->addHtml($this->renderView('pdf_templates/template/template.html.twig', array(
            'templates'  => $templatesSorted,
            'templateTypes'  => $templateTypes,
            'client' => $client,
            'appointment' => $appointment,
            'startup' =>$startupName
        )));

        $fileName = $template->getTemplateType()->getName().'.pdf';
        $this->pdfCreator->getPdfOutput($fileName, 'F');
        $pdf = file_get_contents($fileName);

        $fileUpload = new UploadedFile($fileName,$fileName,null,null,true);


        if($user) {
            $document = $this->documentService->uploadDocument($fileUpload, 'users');
            $user->addDocument($document);
            $this->userRepository->persist($user);

        }
    }



    public function sortTemplates(array $templates): array
    {
        $templatesSorted = array();
        /** @var Template $template */
        foreach($templates as $template)
        {
            $templateTypeId = $template->getTemplateType()->getId();
            if(!isset($templatesSorted[$templateTypeId]))
            {
                $templatesSorted[$templateTypeId] = array();
            }

            $templatesSorted[$templateTypeId][] = $template;
        }

        return $templatesSorted;
    }

    public function formatLines(?Template $template, array $templateLines, array $files, string $directory): array
    {
        $finalTemplateLines = [];
        foreach($templateLines as $index => $templateLine) {
            if ($templateLine['type'] == TemplateLineType::SOURCE_TYPE) {
                $value = @$files[$index]['value'];

                if($value != null){
                    $templateLine['value'] = $this->documentService->uploadDocument($value, $directory)->getId();
                }elseif($template){
                    $templateLine['value'] = $template->getTemplateLineValueByName($templateLine['name']);
                }else{
                    $templateLine['value'] = '';
                }

            } elseif ($templateLine['type'] == TemplateLineType::BOOLEAN_TYPE) {
                $field['value'] = @$templateLine['value'] ? 'Si' : 'No';
            }
            $finalTemplateLines[] = $templateLine;

        }

        return $finalTemplateLines;
    }
}