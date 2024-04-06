<?php


namespace App\Controller\DocumentController;


use App\Controller\UserController\UserController;
use App\Entity\Document\Document;
use App\Service\DocumentService\DocumentService;
use App\Service\UserService\UserService;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/documents')]
class DocumentController extends AbstractController
{

    public function __construct(
        private readonly DocumentService $documentService,
        private readonly UserController $userController

    )
    {
    }

    #[Route(path: '/{document}', name: 'document_render', methods: ["GET"])]
    public function getDocument(string $document):BinaryFileResponse {
        return $this->documentService->renderDocument($document);
    }

    #[Route(path: 'delete/{document}', name: 'document_delete', methods: ["GET", "POST"])]
    public function deleteDocument(string $document, Request $request):Response {
        return $this->documentService->deleteDocument($document, $request);
    }

    #[Route(path: '/upload', name: 'document_upload', methods: ["POST"])]
    public function upload():Document {
        return $this->documentService->uploadRequest();
    }

    #[Route(path: '/download/{documentId}', name: 'document_download', methods: ["GET","POST"])]
    public function download(string $documentId):Response {
        return $this->documentService->downloadDocument($documentId);
    }

    #[Route(path: '/render', name: 'document_get_survey', methods: ["GET","POST"])]
    public function renderSurvey():Response {
        return $this->documentService->renderSurvey();
    }

    #[Route(path: '/save', name: 'document_save_survey', methods: ["GET","POST"])]
    public function saveSurvey(Request $request) {
        return $this->documentService->saveSurvey();
    }

    #[Route(path: '/download/excel/{clientId}{type}', name: 'documents_export', methods: ["GET","POST"])]
    public function exportDocumentsFromClient(string $clientId, string $type, string $surveyRange = null, Request $request) {
        if ($type == 1) {
            return $this->documentService->exportTableToPdf($clientId);
        } elseif ($type == 0) {
            $this->documentService->exportTableToExcel($clientId);
        }
    }

    #[Route(path: '/export/{mentorId}', name: 'documents_export_mentor', methods: ["POST"])]
    public function exportDocumentsFromMentor(string $mentorId = null)
    {
        $this->documentService->exportDocumentsFromAllMentors($mentorId);
    }

}