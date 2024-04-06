<?php


namespace App\Shared\Utils;


use App\Entity\Document\Document;
use App\Service\DocumentService\DocumentService;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DocumentImage
{
    private UrlGeneratorInterface $urlGenerator;
    private DocumentService $documentService;

    public function __construct(UrlGeneratorInterface $urlGenerator, DocumentService $documentService)
    {
        $this->urlGenerator = $urlGenerator;
        $this->documentService = $documentService;
    }

    public function DocumentImage(?Document $document, ?bool $absolute = false) {
        if($document)
            return $this->urlGenerator->generate('document_render', ['document' => $document->getId()], $absolute ? UrlGeneratorInterface::ABSOLUTE_PATH : UrlGeneratorInterface::RELATIVE_PATH);
        return file('assets/images/logo-icono.png');
    }

    public function DocumentImageContent(?Document $document) {
        if($document)
            return $this->documentService->getContentOfDocumentId($document->getId());
        return file_get_contents('assets/images/logo-icono.png');
    }

    public function getDocumentThumbnail(Document $document, int $size = 36) {
        if(!in_array($document->getExtension(), ['doc', 'docx', 'pdf', 'xls', 'xlsx'])) {
            $src = self::DocumentImage($document);
            $name = $document->getFileName();
            return "
                <img src='$src' alt='$name' class='card-img-top' height='$size'>
            ";
        } else {
            $src = file('assets/images/icons/'.$document->getExtension().'.png');
            return "<img src='$src' alt='file-icon' height='$size' />";
        }
    }
}
