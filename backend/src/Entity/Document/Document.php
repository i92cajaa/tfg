<?php

namespace App\Entity\Document;

use App\Entity\Client\ClientHasDocument;
use App\Repository\DocumentRepository;
use App\Shared\Classes\UTCDateTime;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    const STATUS_REMOVED = false;
    const STATUS_ENABLED = true;

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\OneToMany(mappedBy: "document", targetEntity: ClientHasDocument::class, cascade: ["persist", "remove"])]
    private ?Collection $clients;

    #[ORM\Column(name:"original_name", type:"string", length:255, nullable:false)]
    private string $originalName;

    #[ORM\Column(name:"extension", type:"string", length:10, nullable:false)]
    private string $extension;

    #[ORM\Column(name:"mime_type", type:"string", length:50, nullable:true)]
    private ?string $mimeType = null;

    #[ORM\Column(name:"file_name", type:"string", length:255, nullable:false)]
    private string $fileName;

    #[ORM\Column(name:"subdirectory", type:"string", length:255, nullable:false)]
    private string $subdirectory;

    #[ORM\Column(name:"created_at", type:"datetime", nullable:false)]
    private DateTime $createdAt;

    #[ORM\Column(name:"status", type:"boolean", nullable:false)]
    private bool $status;

    #[ORM\Column(name:"is_startup_survey", type:"boolean", nullable:true, options: ['default' => false])]
    private ?bool $isStartupSurvey;

    #[ORM\Column(name:"is_mentor_survey", type:"boolean", nullable:true, options: ['default' => false])]
    private ?bool $isMentorSurvey;

    #[ORM\Column(name:"mentor_survey_points", type:"float", nullable:true, options: ['default' => NULL])]
    private ?float $totalPointsMentorSurvey;

    #[ORM\ManyToOne(targetEntity: SurveyRange::class, inversedBy: Document::class)]
    #[ORM\JoinColumn(name: "survey_range_id", referencedColumnName: "id", onDelete: 'SET NULL')]
    private ?SurveyRange $surveyRange;

    #[ORM\Column(name: "mentored_time", type: "float", nullable: true, options: ['default' => NULL])]
    private ?float $mentoredTime;

    public function __construct()
    {
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());
        $this->status    = true;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     * @return Document
     */
    public function setOriginalName(string $originalName): Document
    {
        $this->originalName = $originalName;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     * @return Document
     */
    public function setExtension(string $extension): Document
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return Document
     */
    public function setFileName(string $fileName): Document
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubdirectory(): string
    {
        return $this->subdirectory;
    }

    /**
     * @param string $subdirectory
     * @return Document
     */
    public function setSubdirectory(string $subdirectory): Document
    {
        $this->subdirectory = $subdirectory;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     * @return Document
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     * @return Document
     */
    public function setStatus(bool $status): Document
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMimetype(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string|null $mimetype
     * @return Document
     */
    public function setMimetype(?string $mimetype): Document
    {
        $this->mimeType = $mimetype;
        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getClients(): ?Collection
    {
        return $this->clients;
    }

    /**
     * @param Collection|null $clients
     */
    public function setClients(?Collection $clients): void
    {
        $this->clients = $clients;
    }

    /**
     * @return bool|null
     */
    public function isStartUpSurvey() : ?bool
    {
        return $this->isStartupSurvey;
    }

    /**
     * @param bool|null $isStartupSurvey
     * @return $this
     */
    public function setIsStartupSurvey(?bool $isStartupSurvey) : Document
    {
        $this->isStartupSurvey = $isStartupSurvey;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isMentorSurvey() : ?bool
    {
        return $this->isMentorSurvey;
    }

    /**
     * @param bool|null $isMentorSurvey
     * @return $this
     */
    public function setIsMentorSurvey(?bool $isMentorSurvey) : Document
    {
        $this->isMentorSurvey = $isMentorSurvey;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalPointsMentorSurvey() : ?float
    {
        return $this->totalPointsMentorSurvey;
    }

    /**
     * @param float|null $totalPointsMentorSurvey
     * @return $this
     */
    public function setTotalPointsMentorSurvey(?float $totalPointsMentorSurvey) : Document
    {
        $this->totalPointsMentorSurvey = $totalPointsMentorSurvey;

        return $this;
    }

    /**
     * @return SurveyRange|null
     */
    public function getSurveyRange() : ?SurveyRange
    {
        return $this->surveyRange;
    }

    /**
     * @param SurveyRange|null $surveyRange
     * @return $this
     */
    public function setSurveyRange(?SurveyRange $surveyRange) : Document
    {
        $this->surveyRange = $surveyRange;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getMentoredTime(): ?float
    {
        return $this->mentoredTime;
    }

    /**
     * @param float|null $mentoredTime
     * @return Document
     */
    public function setMentoredTime(?float $mentoredTime): Document
    {
        $this->mentoredTime = $mentoredTime;
        return $this;
    }
}
