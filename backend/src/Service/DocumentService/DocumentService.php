<?php


namespace App\Service\DocumentService;


use App\Entity\Document\Document;
use App\Entity\Document\SurveyRange;
use App\Entity\User\User;
use App\Entity\User\UserHasClient;
use App\Entity\User\UserHasDocument;
use App\Repository\AppointmentRepository;
use App\Repository\ClientHasDocumentRepository;
use App\Repository\ClientRepository;
use App\Repository\DocumentRepository;
use App\Repository\ServiceRepository;
use App\Repository\SurveyRangeRepository;
use App\Repository\UserHasClientRepository;
use App\Repository\UserHasDocumentRepository;
use App\Repository\UserRepository;
use App\Shared\Classes\AbstractService;
use App\Shared\Utils\PdfCreator;
use App\Shared\Utils\UploadFile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use phpDocumentor\Reflection\Types\This;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class DocumentService extends AbstractService
{
    const UPLOADS_DIR = 'uploads';

    private string $storagePath;
    private string $documentPath;
    private string $publicPath;

    public function __construct(
        private readonly KernelInterface $appKernel,
        private readonly Filesystem $filesystem,
        private readonly UserRepository $userRepository,
        private readonly DocumentRepository $documentRepository,
        private readonly ClientHasDocumentRepository $clientHasDocumentRepository,
        private readonly UserHasDocumentRepository $userHasDocumentRepository,
        private readonly AppointmentRepository $appointmentRepository,
        private readonly ClientRepository $clientRepository,
        private readonly PdfCreator $pdfCreator,
        private readonly SurveyRangeRepository $surveyRangeRepository,
        private readonly ServiceRepository $serviceRepository,

        RouterInterface       $router,
        Environment           $twig,
        RequestStack          $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface      $tokenManager,
        FormFactoryInterface           $formFactory,
        SerializerInterface            $serializer,
        TranslatorInterface $translator,
        protected KernelInterface $kernel
    )
    {
        $this->publicPath        = $this->appKernel->getProjectDir().'/public';
        $this->storagePath        = $this->appKernel->getProjectDir().'/resources';
        $this->documentPath        = $this->storagePath .'/documents';

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

    public function uploadDocument(UploadedFile $file,
                                   string $subdirectory,
                                   bool $isStartupSurvey = null,
                                   bool $isMentorSurvey = null,
                                   float $totalPointsMentorSurvey = null,
                                   SurveyRange $surveyRange = null,
                                   float $timeMentored = null): Document
    {
        $uploadPath   = $this->documentPath . '/' . $subdirectory;
        $uploadedFile = UploadFile::upload($file, $uploadPath);
        return $this->documentRepository->createDocument(
            $file->getClientOriginalName(),
            $uploadedFile['fileName'],
            $uploadedFile['extension'],
            $file->getClientMimeType(),
            $subdirectory,
            true,
            $isStartupSurvey,
            $isMentorSurvey,
            $totalPointsMentorSurvey,
            $surveyRange,
            $timeMentored
        );
    }

    public function renderDocument(string $documentId): BinaryFileResponse
    {
        $document = $this->documentRepository->findDocument($documentId);
        if ($document && $this->filesystem->exists($this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName())) {
            return new BinaryFileResponse($this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName());
        } else {

            return new BinaryFileResponse('assets/images/not_found.svg');
        }
    }

    public function downloadDocument(string $documentId): BinaryFileResponse
    {
        $document = $this->documentRepository->findDocument($documentId);
        if ($document && $this->filesystem->exists($this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName())) {
            $response = new BinaryFileResponse($this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName());
        } else {
            throw new FileNotFoundException($document->getOriginalName());
        }

        $mimeTypeGuesser = new FileinfoMimeTypeGuesser();

        // Set the mimetype with the guesser or manually
        if($mimeTypeGuesser->isGuesserSupported()){
            // Guess the mimetype of the file according to the extension of the file
            $response->headers->set('Content-Type', $mimeTypeGuesser->guessMimeType($this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName()));
        }else{
            // Set the mimetype of the file manually, in this case for a text file is text/plain
            $response->headers->set('Content-Type', 'text/plain');
        }

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $document->getOriginalName()
        );

        return $response;
    }

    public function getContentOfDocumentByUrl(string $documentUrl): ?string
    {
        if ($this->filesystem->exists($this->storagePath . '/' . $documentUrl)) {
            return file_get_contents($this->storagePath . '/' . $documentUrl);
        } else {
            return null;
        }
    }

    public function getContentOfDocumentId(string $documentId): ?string
    {
        $document = $this->documentRepository->find($documentId);
        if($document){
            if ($this->filesystem->exists($this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName())) {
                return file_get_contents($this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName());
            } else {
                return null;
            }
        }
        return null;
    }

    public function getContentOfPublicAssetByUrl(string $path): ?string
    {

        if ($this->filesystem->exists($this->publicPath . '/' . $path)) {
            return file_get_contents($this->publicPath . '/' . $path);
        } else {
            return null;
        }

    }

    public function getDocumentUrl(string $documentId, ?bool $nullable = false): ?string
    {
        $document = $this->documentRepository->findDocument($documentId);
        if ($this->filesystem->exists($this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName())) {
            return $this->documentPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName();
        } else {
            if($nullable){
                return null;
            }
            return $this->documentPath . '/images/no_image.png';
        }
    }

    public function getPublicFileAbsoluteUrl(string $pathToFile)
    {
        return $this->publicPath . '/' . $pathToFile;
    }

    public function getPublicAbsoluteUrl()
    {
        return $this->publicPath;
    }

    public function uploadRequest($filename = 'document'): Document
    {
        $fileData = $this->getCurrentRequest()->files->get('document');
        if($filename === 'document'){
            $filename = $fileData->getClientOriginalName();
        }
        $file = new UploadedFile(
            $fileData->getPathname(),
            $filename,
            $fileData->getMimeType(),
            0,
            true // Mark it as test, since the file isn't from real HTTP POST.
        );

        return $this->uploadDocument($file, self::UPLOADS_DIR);

//        return new JsonResponse($this->generateUrl('document_render', ['document' => $document->getId()], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function deleteDocument(string $documentId, Request $request)
    {
        $document = $this->documentRepository->find($documentId);
        if ($document) $this->documentRepository->deleteDocument($document);
        if ($document && $this->filesystem->exists($this->storagePath . '/documents/' . $document->getSubdirectory() . '/' . $document->getFileName())) {
            UploadFile::delete($this->storagePath . '/documents/' . $document->getSubdirectory() . '/' . $document->getFileName());
        }

        return $this->redirect($request->headers->get('referer'));
    }

    public function deleteDocumentJson(string $documentId): JsonResponse
    {
        $document = $this->documentRepository->find($documentId);
        $this->documentRepository->deleteDocument($document);
        if ($this->filesystem->exists($this->publicPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName())) {
            UploadFile::delete($this->publicPath . '/' . $document->getSubdirectory() . '/' . $document->getFileName());
        }

        return new JsonResponse('');
    }

    public function getDocumentById(string $documentId): ?Document
    {
        return $this->documentRepository->find($documentId);
    }

    /**
     * Carga el documento en el modal
     *
     * @return JsonResponse
     */
    public function renderSurvey()
    {
        if ($this->getRequestPostParam('surveyType') == 'startup') {

            $name = $this->getUser()->getClient()->getName();
            $ceo = $this->getUser()->getClient()->getRepresentative();
            $position = $this->getUser()->getClient()->getPosition();


            $document = $this->renderView('document/startup_survey.html.twig', [
                'startupName' => $name,
                'startupCEO' => $ceo,
                'uri' => $this->getRequestPostParam('uri'),
                'position' => $position
            ]);

            return new JsonResponse(['success' => true, 'data' => $document, 'message' => 'Plantilla de creación de cuestionarios']);
        }

        if ($this->getRequestPostParam('surveyType') == 'mentor') {

            $user = $this->userRepository->find($this->getRequestPostParam('mentor'));

            $document = $this->renderView('document/mentor_survey.html.twig', [
                'user' => $user,
                'hours' => $this->getRequestPostParam('hours'),
                'minutes' => $this->getRequestPostParam('minutes'),
                'areaName' => $this->getRequestPostParam('area')
            ]);

            return new JsonResponse(['success' => true, 'data' => $document, 'message' => 'Plantilla de creación de cuestionarios']);
        }
    }


    public function saveSurvey()
    {
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

        $css = $this->getContentOfPublicAssetByUrl('assets/css/vendors/bootstrap.css');
        $this->pdfCreator->addCss($css);


        $css = $this->getContentOfPublicAssetByUrl('assets/css/vendors/quill/quill.snow.css');
        $this->pdfCreator->addCss($css);

        $this->pdfCreator->setFooter($this->renderView('pdf_templates/template/footer.html.twig', [
        ]));

        $this->pdfCreator->setHeader($this->renderView('pdf_templates/template/header.html.twig', [
        ]));

        if ($this->getRequestPostParam('type') == 'startup') {
            $startupName = $this->getRequestPostParam('startup_name');
            $startupCEO = $this->getRequestPostParam('startup_CEO');
            $documentLines = $this->getRequestPostParam('document_lines');
            $position = $this->getRequestPostParam('position');

            $this->pdfCreator->addHtml($this->renderView('pdf_templates/surveys/startup_survey_pdf.html.twig', array(
                'startupName'  => $startupName,
                'startupCEO'  => $startupCEO,
                'document_lines' => $documentLines,
                'position' => $position
            )));

            $fileName = 'cuestionario_startup-'.$startupName.'.pdf';

            $existsDocument = $this->documentRepository->findOneBy(['originalName' => $fileName]);

            if ($existsDocument == null) {
                $this->pdfCreator->getPdfOutput($fileName, 'F');
                $pdf = file_get_contents($fileName);

                $fileUpload = new UploadedFile($fileName,$fileName,null,null,true);

                $document = $this->uploadDocument($fileUpload, 'users', true);

                $this->clientHasDocumentRepository->addDocumentToClient($document, $this->getUser()->getClient());
            }

            return $this->redirectToRoute('user_all_surveys');
        } elseif ($this->getRequestPostParam('type') == 'mentor') {
            $mentorName = $this->getRequestPostParam('mentor_name');
            $appointmentArea = $this->getRequestPostParam('appointment_area');
            $appointmentTime = $this->getRequestPostParam('appointment_time');
            $documentLines = $this->getRequestPostParam('document_lines');

            $this->pdfCreator->addHtml($this->renderView('pdf_templates/surveys/mentor_survey_pdf.html.twig', array(
                'mentorName'  => $mentorName,
                'appointmentArea' => $appointmentArea,
                'appointmentTime' => $appointmentTime,
                'document_lines' => $documentLines
            )));

            $points = [];

            for ($i = 0; $i < 4; $i++) {
                if ($i == 2) {
                    for ($j = 0; $j < 2; $j++) {
                        if ($documentLines[$i]['array'][$j]['select'] == 'Insuficiente') {
                            $points[$i][$j] = 0.2;
                        } elseif ($documentLines[$i]['array'][$j]['select'] == 'Mejorable') {
                            $points[$i][$j] = 0.4;
                        } elseif ($documentLines[$i]['array'][$j]['select'] == 'Aceptable') {
                            $points[$i][$j] = 0.6;
                        } elseif ($documentLines[$i]['array'][$j]['select'] == 'Buena') {
                            $points[$i][$j] = 0.8;
                        } elseif ($documentLines[$i]['array'][$j]['select'] == 'Muy Buena') {
                            $points[$i][$j] = 1;
                        }
                    }
                } else {
                    if ($documentLines[$i]['select'] == 'Insuficiente' or $documentLines[$i]['select'] == '1') {
                        $points[$i] = 0.2;
                    } elseif ($documentLines[$i]['select'] == 'Mejorable' or $documentLines[$i]['select'] == '2') {
                        $points[$i] = 0.4;
                    } elseif ($documentLines[$i]['select'] == 'Aceptable' or $documentLines[$i]['select'] == '3') {
                        $points[$i] = 0.6;
                    } elseif ($documentLines[$i]['select'] == 'Buena' or $documentLines[$i]['select'] == '4') {
                        $points[$i] = 0.8;
                    } elseif ($documentLines[$i]['select'] == 'Muy Buena' or $documentLines[$i]['select'] == '5') {
                        $points[$i] = 1;
                    }
                }
            }

            $points[0] *= 0.2;
            $points[1] *= 0.4;
            $points[2][0] *= 0.15;
            $points[2][1] *= 0.05;
            $points[3] *= 0.2;

            $totalPoints = ($points[0] + $points[1] + $points[2][0] + $points[2][1] + $points[3]) * 10;

            $activeSurveyRange = $this->surveyRangeRepository->findOneBy(['status' => true]);
            $surveyRangeFrom = ($activeSurveyRange->getStartDate() == null) ? 'any' : $activeSurveyRange->getStartDate()->format('d-m-Y');
            $surveyRangeTo = ($activeSurveyRange->getEndDate() == null) ? 'any' : $activeSurveyRange->getEndDate()->format('d-m-Y');

            $fileName = 'cuestionario_mentor-'.$mentorName.'-'.$this->getUser()->getClient()->getName().'-'.$surveyRangeFrom.'_to_'.$surveyRangeTo.'.pdf';
            $this->pdfCreator->getPdfOutput($fileName, 'F');
            $pdf = file_get_contents($fileName);

            $mentor = $this->userRepository->find($this->getRequestPostParam('userId'));

            $fileUpload = new UploadedFile($fileName,$fileName,null,null,true);

            $document = $this->uploadDocument($fileUpload, 'users', false, true, $totalPoints, $activeSurveyRange, floatval($this->getRequestPostParam('appointment_time_float')));

            $this->clientHasDocumentRepository->addDocumentToClient($document, $this->getUser()->getClient());

            $this->userHasDocumentRepository->addDocumentToUser($document, $mentor);

            return $this->redirectToRoute('user_all_surveys');
        }
    }

    public function exportTableToExcel(string $clientId)
    {
        // Crear un nuevo objeto Spreadsheet (libro de Excel)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar encabezados de columna
        $sheet->setCellValue('A1', 'Nombre');
        $sheet->setCellValue('B1', 'Mentor');
        $sheet->setCellValue('C1', 'Proyecto');
        $sheet->setCellValue('D1', 'Puntuación');
        $sheet->setCellValue('E1', 'Fecha Creación');

        $client = $this->clientRepository->find($clientId);
        $clientHasDocuments = $this->clientHasDocumentRepository->findBy(['client' => $client]);

        $documents = [];
        foreach ($clientHasDocuments as $clientHasDocument) {
            if ($clientHasDocument->getDocument()->isMentorSurvey() &&
                $clientHasDocument->getDocument()->getSurveyRange() != null &&
                $clientHasDocument->getDocument()->getSurveyRange()->getId() == $this->getRequestPostParam('surveyRange')) {

                $documents[] = $clientHasDocument->getDocument();
            } elseif ($clientHasDocument->getDocument()->isMentorSurvey() &&
                $this->getRequestPostParam('surveyRange') == '') {

                $documents[] = $clientHasDocument->getDocument();
            }
        }

        // Obtener los datos de la tabla y agregarlos al archivo Excel
        $row = 2;
        foreach ($documents as $document) {
            $names = explode('-', $document->getOriginalName());
            $project = explode('.', $names[2]);
            // Agregar datos de cada fila
            $sheet->setCellValue('A' . $row, $document->getOriginalName());
            $sheet->setCellValue('B' . $row, $names[1]);
            $sheet->setCellValue('C' . $row, $project[0]);
            $sheet->setCellValue('D' . $row, $document->getTotalPointsMentorSurvey());
            $sheet->setCellValue('E' . $row, $document->getCreatedAt()->format('d-m-Y - H:i'));

            $row++;
        }

        // Configurar la respuesta HTTP para descargar el archivo Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'exportacion_tabla.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // Terminar la ejecución de la respuesta
        exit();
    }

    public function exportTableToPdf(string $clientId)
    {
        // Crear un nuevo objeto Spreadsheet (libro de Excel)
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

        $css = $this->getContentOfPublicAssetByUrl('assets/css/vendors/bootstrap.css');
        $this->pdfCreator->addCss($css);


        $css = $this->getContentOfPublicAssetByUrl('assets/css/vendors/quill/quill.snow.css');
        $this->pdfCreator->addCss($css);

        $this->pdfCreator->setFooter($this->renderView('pdf_templates/template/footer.html.twig', [
        ]));

        $this->pdfCreator->setHeader($this->renderView('pdf_templates/template/header.html.twig', [
        ]));

        $client = $this->clientRepository->find($clientId);
        $clientHasDocuments = $this->clientHasDocumentRepository->findBy(['client' => $client]);

        $documents = [];
        foreach ($clientHasDocuments as $clientHasDocument) {
            if ($clientHasDocument->getDocument()->isMentorSurvey() &&
                $clientHasDocument->getDocument()->getSurveyRange() != null &&
                $clientHasDocument->getDocument()->getSurveyRange()->getId() == $this->getRequestPostParam('surveyRange')) {

                $documents[] = $clientHasDocument->getDocument();
            } elseif ($this->getRequestPostParam('surveyRange') == '' &&
                $clientHasDocument->getDocument()->isMentorSurvey()) {

                $documents[] = $clientHasDocument->getDocument();
            }
        }

        $this->pdfCreator->addHtml($this->renderView('pdf_templates/surveys/export_mentor_survey_table.html.twig', array(
            'documents'  => $documents
        )));

        $fileName = 'tabla_cuestionarios_mentores.pdf';
        $this->pdfCreator->getPdfOutput($fileName, 'F');
        $pdf = file_get_contents($fileName);

        $fileUpload = new UploadedFile($fileName,$fileName,null,null,true);

        $document = $this->uploadDocument($fileUpload, 'users');

        return $this->downloadDocument($document->getId());
    }

    public function exportDocumentsFromMentor(User $mentor)
    {

        $surveyRange = null;
        if ($this->getRequestPostParam('surveyRange') != 'Todos') {
            $surveyRange = $this->surveyRangeRepository->find($this->getRequestPostParam('surveyRange'));
        }

        $mentorHasDocuments = $this->userHasDocumentRepository->findBy(['user' => $mentor]);

        $documents = [];
        foreach ($mentorHasDocuments as $mentorHasDocument) {
            if ($mentorHasDocument->getDocument()->isMentorSurvey() && $surveyRange != null &&
                $mentorHasDocument->getDocument()->getSurveyRange()->getId() == $surveyRange->getId()) {

                $documents[] = $mentorHasDocument->getDocument();
            } elseif ($mentorHasDocument->getDocument()->isMentorSurvey() && $surveyRange == null) {

                $documents[] = $mentorHasDocument->getDocument();
            }
        }

        if ($surveyRange != null) {
            if ($surveyRange->getStartDate() != null) $this->filterService->addFilter('date_from', $surveyRange->getStartDate()->format('d-m-Y'));
            if ($surveyRange->getEndDate() != null) $this->filterService->addFilter('date_to', $surveyRange->getEndDate()->format('d-m-Y'));
        }

        $this->filterService->addFilter('user', $mentor->getId());
        $this->filterService->addFilter('statusType', 9);
        $this->filterService->addFilter('services', $this->serviceRepository->findBy(['forAdmin'=>false, 'forClient' => false]));

        $appointments = $this->appointmentRepository->findAppointments($this->filterService, true);

        $hours = 0;
        $minutes = 0;
        $centers = [];
        $numberOfProjectsMentor = [];
        foreach ($appointments['data'] as $appointment) {
            $timeDiff = $appointment->getTimeTo()->diff($appointment->getTimeFrom());

            $hours += $timeDiff->h;
            $minutes += $timeDiff->i;

            if ($minutes >= 60) {
                $hours++;
                $minutes -= 60;
            }

            if (!in_array($appointment->getCenter()->getName(), $centers)) {
                $centers[] = $appointment->getCenter()->getName();
            }

            if ($appointment->getClient() != null && !in_array($appointment->getClient()->getId(), $numberOfProjectsMentor)) {
                $numberOfProjectsMentor[] = $appointment->getClient()->getId();
            }
        }

        $totalTime = $hours . 'h';
        if ($minutes != 0) $totalTime .= ' ' . $minutes . 'min';

        $numerator = 0;
        $denominator = 0;
        foreach ($documents as $document) {
            $numerator += $document->getTotalPointsMentorSurvey() * $document->getMentoredTime();
            $denominator += $document->getMentoredTime();
        }

        $average = ($denominator === 0) ? 0 : number_format($numerator / $denominator, 2);

        return [
            $mentor->getName() . ' ' . $mentor->getSurnames(),
            $totalTime,
            count($numberOfProjectsMentor),
            count($documents),
            $average,
            implode(', ', $centers)
        ];

    }

    public function exportDocumentsFromAllMentors(?string $mentorId)
    {

        $surveyRangeString = '';
        if ($this->getRequestPostParam('surveyRange') != 'Todos') {
            $surveyRange = $this->surveyRangeRepository->find($this->getRequestPostParam('surveyRange'));

            $surveyRangeString = '_' . $surveyRange->getStartDate()->format('d-m-Y') . '_a_' . $surveyRange->getEndDate()->format('d-m-Y');
        }

        $rows = [];
        if ($mentorId == null) {
            $this->filterService->addFilter('roles', ['3']);

            $mentors = $this->userRepository->findUsers($this->filterService, true);

            $selectedMentorsId = $this->getRequestPostParam('mentors');
            if ($selectedMentorsId[0] != 'Todos') {
                foreach ($mentors['data'] as $key => $mentor) {
                    $found = false;

                    foreach ($selectedMentorsId as $id) {
                        if ($id == $mentor->getId()) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) unset($mentors['data'][$key]);
                }
            }

            foreach ($mentors['data'] as $mentor) {
                $rows[] = $this->exportDocumentsFromMentor($mentor);
            }

            $filename = 'exportacion_mentores_' . $surveyRangeString . '.xlsx';
        } else {
            $mentor = $this->userRepository->find($mentorId);
            $rows[] = $this->exportDocumentsFromMentor($mentor);

            $filename = 'exportacion_mentor_' . $mentor->getName() . $surveyRangeString . '.xlsx';
        }

        // Crear un nuevo objeto Spreadsheet (libro de Excel)
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar encabezados de columna
        $sheet->setCellValue('A1', 'Mentor');
        $sheet->setCellValue('B1', 'Nº Horas');
        $sheet->setCellValue('C1', 'Nº Proyectos');
        $sheet->setCellValue('D1', 'Nº Cuestionarios completados');
        $sheet->setCellValue('E1', 'Nota Media');
        $sheet->setCellValue('F1', 'HUB');

        // Obtener los datos de la tabla y agregarlos al archivo Excel
        $row = 2;
        foreach ($rows as $rowContent) {
            // Agregar datos de cada fila
            $sheet->setCellValue('A' . $row, $rowContent[0]);
            $sheet->setCellValue('B' . $row, $rowContent[1]);
            $sheet->setCellValue('C' . $row, $rowContent[2]);
            $sheet->setCellValue('D' . $row, $rowContent[3]);
            $sheet->setCellValue('E' . $row, $rowContent[4]);
            $sheet->setCellValue('F' . $row, $rowContent[5]);

            $row++;
        }

        // Configurar la respuesta HTTP para descargar el archivo Excel
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // Terminar la ejecución de la respuesta
        exit();
    }
}