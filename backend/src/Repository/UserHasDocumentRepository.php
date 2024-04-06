<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\Client\ClientHasDocument;
use App\Entity\Document\Document;
use App\Entity\User\User;
use App\Entity\User\UserHasDocument;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @method UserHasDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserHasDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserHasDocument[]    findAll()
 * @method UserHasDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserHasDocumentRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserHasDocument::class);
    }

    /**
     * Generates a clientHasDocument entity and saves it
     *
     * @param Document $document
     * @param User $user
     * @return ClientHasDocument
     */
    public function addDocumentToUser(
        Document $document,
        User $user
    ) : UserHasDocument
    {
        $userHasDocument = (new UserHasDocument())
            ->setUser($user)
            ->setDocument($document);

        $this->save($this->_em, $userHasDocument);

        return $userHasDocument;
    }
}