<?php

namespace App\Entity\Document;

use App\Repository\SurveyRangeRepository;
use App\Shared\Classes\UTCDateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\Uid\Uuid;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyRangeRepository::class)]
class SurveyRange
{

    const STATUS_DISABLED = false;
    const STATUS_ENABLED = true;

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(name:"start_date", type:"datetime", nullable:true, options: ['default' => NULL])]
    private ?DateTime $startDate;
    #[ORM\Column(name:"end_date", type:"datetime", nullable:true, options: ['default' => NULL])]
    private ?DateTime $endDate;
    #[ORM\Column(name:"status", type:"boolean", nullable:false, options: ['default' => true])]
    private bool $status;

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return DateTime|null
     */
    public function getStartDate() : ?DateTime
    {
        return $this->startDate;
    }

    /**
     * @param DateTime|null $startDate
     * @return $this
     */
    public function setStartDate(?DateTime $startDate) : SurveyRange
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getEndDate() : ?DateTime
    {
        return $this->endDate;
    }

    /**
     * @param DateTime|null $endDate
     * @return $this
     */
    public function setEndDate(?DateTime $endDate) : SurveyRange
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function getStatus() : bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setStatus(bool $status) : SurveyRange
    {
        $this->status = $status;

        return $this;
    }
}