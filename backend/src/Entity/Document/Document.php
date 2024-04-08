<?php

namespace App\Entity\Document;

use App\Entity\Client\Client;
use App\Entity\Client\ClientHasDocument;
use App\Repository\DocumentRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{

    // ----------------------------------------------------------------
    // Constants
    // ----------------------------------------------------------------

    const STATUS_REMOVED = false;
    const STATUS_ENABLED = true;

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    #[ORM\OneToMany(mappedBy: "document", targetEntity: ClientHasDocument::class, cascade: ["persist", "remove"])]
    private array|Collection $clients;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

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

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());
        $this->status    = true;
    }

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

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
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getSubdirectory(): string
    {
        return $this->subdirectory;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getMimetype(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @return Collection|null
     */
    public function getClients(): array|Collection
    {
        return $this->clients;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): Document
    {
        $this->id = $id;
        return $this;
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
     * @param string $extension
     * @return Document
     */
    public function setExtension(string $extension): Document
    {
        $this->extension = $extension;
        return $this;
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
     * @param string $subdirectory
     * @return Document
     */
    public function setSubdirectory(string $subdirectory): Document
    {
        $this->subdirectory = $subdirectory;
        return $this;
    }

    /**
     * @param DateTime $createdAt
     * @return Document
     */
    public function setCreatedAt(DateTime $createdAt): Document
    {
        $this->createdAt = $createdAt;
        return $this;
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
     * @param string|null $mimetype
     * @return Document
     */
    public function setMimetype(?string $mimetype): Document
    {
        $this->mimeType = $mimetype;
        return $this;
    }

    /**
     * @param array|Collection $clients
     * @return Document
     */
    public function setClients(array|Collection $clients): Document
    {
        $this->clients = $clients;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD CLIENT TO DOCUMENT
     * ES: FUNCIÓN PARA AÑADIR CLIENTE AL DOCUMENTO
     *
     * @param Client $client
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addClient(Client $client): Document
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE CLIENT FROM DOCUMENT
     * ES: FUNCIÓN PARA BORRAR CLIENTE DEL DOCUMENTO
     *
     * @param Client $client
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeClient(Client $client): Document
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
        }

        return $this;
    }
    // ----------------------------------------------------------------

}
