<?php

namespace App\Repository;

use App\Entity\Document\Document;
use App\Entity\Document\SurveyRange;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use App\Service\FilterService;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\This;


class DocumentRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * @param string $originalName
     * @param string $fileName
     * @param string $extension
     * @param string $mimetype
     * @param string $subdirectory
     * @param bool $status
     * @param bool|null $isStartupSurvey
     * @param bool|null $isMentorSurvey
     * @param float|null $totalPointsMentorSurvey
     * @param SurveyRange|null $surveyRange
     * @param int|null $mentoredTime
     * @return Document
     */
    public function createDocument(
        string $originalName,
        string $fileName,
        string $extension,
        string $mimetype,
        string $subdirectory,
        bool $status = true,
        bool $isStartupSurvey = null,
        bool $isMentorSurvey = null,
        float $totalPointsMentorSurvey = null,
        SurveyRange $surveyRange = null,
        float $mentoredTime = null
    ): Document
    {
        $document = (new Document())
            ->setOriginalName($originalName)
            ->setFileName($fileName)
            ->setExtension($extension)
            ->setMimetype($mimetype)
            ->setSubdirectory($subdirectory)
            ->setStatus($status)
            ->setIsStartupSurvey($isStartupSurvey)
            ->setIsMentorSurvey($isMentorSurvey)
            ->setTotalPointsMentorSurvey($totalPointsMentorSurvey)
            ->setSurveyRange($surveyRange)
            ->setMentoredTime($mentoredTime);

        $this->save($this->_em, $document);

        return $document;
    }

    /**
     * @param string $documentId
     * @return object|Document
     */
    public function findDocument(string $documentId): ?Document
    {
        return $this->find($documentId);
    }

    public function deleteDocument(Document $document): void
    {
        $this->delete($this->_em, $document);
    }

    /**
     * @param Document $document
     * @return void
     */
    public function addSurveyRangeToDocument(Document $document, SurveyRange $surveyRange): void
    {
        $document = $this->findDocument($document->getId());

        $document->setSurveyRange($surveyRange);

        $this->_em->persist($document);
        $this->_em->flush();
    }

    /**
     * @param Document $document
     * @return void
     */
    public function saveDocument(Document $document): void
    {
        $this->save($this->_em, $document);
    }

}
