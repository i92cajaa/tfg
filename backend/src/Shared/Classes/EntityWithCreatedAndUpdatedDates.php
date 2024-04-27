<?php

namespace App\Shared\Classes;

use DateTime;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

#[HasLifecycleCallbacks]
class EntityWithCreatedAndUpdatedDates
{
    #[Column(name: "created_at", type: "datetime", unique: false, nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected ?DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", unique: false, nullable: true)]
    protected ?DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return ?DateTime
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param ?DateTime $createdAt
     * @return EntityWithCreatedAndUpdatedDates
     */
    public function setCreatedAt(?DateTime $createdAt): EntityWithCreatedAndUpdatedDates
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return ?DateTime
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param ?DateTime $updatedAt
     * @return EntityWithCreatedAndUpdatedDates
     */
    public function setUpdatedAt(?DateTime $updatedAt): EntityWithCreatedAndUpdatedDates
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


}