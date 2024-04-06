<?php

namespace App\Entity\Token;

use App\Repository\TokenRepository;
use App\Shared\Classes\UTCDateTime;
use DateTime;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token
{
    const AZURE_AUTHENTICATION_TAG = 'azure_auth_token';
    const AZURE_ACCESS_TAG = 'azure_access_token';

    const AZURE_AUTHENTICATION_TOKEN_TYPE = 'Oauth V2';
    const AZURE_ACCESS_TOKEN_TYPE = 'Bearer';


    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;


    // Campos

    #[ORM\Column(name:"tag", type:"string", length: 255, nullable: false)]
    private string $tag;

    #[ORM\Column(name:"token_type", type:"string", nullable: true)]
    private ?string $tokenType;

    #[ORM\Column(name:"expiration_date", type:"datetime", nullable: true)]
    private ?DateTime $expirationDate;

    #[ORM\Column(name:"ext_expiration_date", type:"datetime", nullable: true)]
    private ?DateTime $extExpirationDate;

    #[ORM\Column(name:"default_value", type:"text", nullable: true)]
    private ?string $token;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Token
     */
    public function setId(string $id): Token
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     * @return Token
     */
    public function setTag(string $tag): Token
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenType(): ?string
    {
        return $this->tokenType;
    }

    /**
     * @param ?string $tokenType
     * @return Token
     */
    public function setTokenType(?string $tokenType): Token
    {
        $this->tokenType = $tokenType;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getExpirationDate(): ?DateTime
    {
        return $this->expirationDate;
    }

    /**
     * @param DateTime|null $expirationDate
     * @return Token
     */
    public function setExpirationDate(?DateTime $expirationDate): Token
    {
        $this->expirationDate = UTCDateTime::setUTC($expirationDate);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtExpirationDate(): ?DateTime
    {
        return $this->extExpirationDate;
    }

    /**
     * @param DateTime|null $extExpirationDate
     * @return Token
     */
    public function setExtExpirationDate(?DateTime $extExpirationDate): Token
    {
        $this->extExpirationDate = UTCDateTime::setUTC($extExpirationDate);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     * @return Token
     */
    public function setToken(?string $token): Token
    {
        $this->token = $token;
        return $this;
    }




}
