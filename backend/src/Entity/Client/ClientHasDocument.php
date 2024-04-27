<?php

namespace App\Entity\Client;

use App\Entity\Document\Document;
use App\Repository\ClientHasDocumentRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ClientHasDocumentRepository::class)]
class ClientHasDocument
{

    // ----------------------------------------------------------------
    // Primary Keys
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Client $client;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Document::class, inversedBy: 'clients')]
    #[ORM\JoinColumn(name: "document_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Document $document;

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param Client $client
     * @return ClientHasDocument
     */
    public function setClient(Client $client): ClientHasDocument
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param Document $document
     * @return ClientHasDocument
     */
    public function setDocument(Document $document): ClientHasDocument
    {
        $this->document = $document;

        return $this;
    }
}
