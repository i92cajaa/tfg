<?php

namespace App\Entity\User;

use App\Entity\User\User;
use App\Entity\Document\Document;
use App\Repository\UserHasDocumentRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: UserHasDocumentRepository::class)]
class UserHasDocument
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'documents')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Document::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: "document_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Document $document;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): UserHasDocument
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @param Document $document
     */
    public function setDocument(Document $document): UserHasDocument
    {
        $this->document = $document;

        return $this;
    }




}
