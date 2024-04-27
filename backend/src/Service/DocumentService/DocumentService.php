<?php


namespace App\Service\DocumentService;


use App\Entity\Document\Document;
use App\Entity\User\User;
use App\Repository\ClientHasDocumentRepository;
use App\Repository\ClientRepository;
use App\Repository\DocumentRepository;
use App\Repository\UserHasDocumentRepository;
use App\Repository\UserRepository;
use App\Shared\Classes\AbstractService;
use App\Shared\Utils\PdfCreator;
use App\Shared\Utils\UploadFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
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
        private readonly ClientRepository $clientRepository,
        private readonly PdfCreator $pdfCreator,

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
                                   string $subdirectory): Document
    {
        $uploadPath   = $this->documentPath . '/' . $subdirectory;
        $uploadedFile = UploadFile::upload($file, $uploadPath);
        return $this->documentRepository->createDocument(
            $file->getClientOriginalName(),
            $uploadedFile['fileName'],
            $uploadedFile['extension'],
            $file->getClientMimeType(),
            $subdirectory
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
}