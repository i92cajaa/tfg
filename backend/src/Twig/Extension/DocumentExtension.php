<?php


namespace App\Twig\Extension;


use App\Repository\ConfigRepository;
use App\Service\DocumentService\DocumentService;
use App\Shared\Utils\DocumentImage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class DocumentExtension extends AbstractExtension implements GlobalsInterface
{


    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly DocumentService $documentService
    )
    {

    }

    public function getGlobals():array
    {

        return [
            'documentImage' => new DocumentImage($this->urlGenerator, $this->documentService)
        ];
    }


}