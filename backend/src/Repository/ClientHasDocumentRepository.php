<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Client\ClientHasDocument;
use App\Entity\Document\Document;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @method ClientHasDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientHasDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientHasDocument[]    findAll()
 * @method ClientHasDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientHasDocumentRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientHasDocument::class);
    }

    /**
     * Generates a clientHasDocument entity and saves it
     *
     * @param Document $document
     * @param Client $client
     * @return ClientHasDocument
     */
    public function addDocumentToClient(
        Document $document,
        Client $client
    ) : ClientHasDocument
    {
        $clientHasDocument = (new ClientHasDocument())
            ->setClient($client)
            ->setDocument($document);

        $this->save($this->_em, $clientHasDocument);

        return $clientHasDocument;
    }
}