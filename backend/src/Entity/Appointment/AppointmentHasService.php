<?php

namespace App\Entity\Appointment;

use App\Entity\Service\Service;
use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class AppointmentHasService
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Appointment::class, inversedBy: 'services')]
    #[ORM\JoinColumn(name: "appointment_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Appointment $appointment;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(name: "service_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Service $service;

    // Campos

    #[ORM\Column(type:"float", nullable:true)]
    private ?float $iva;

    #[ORM\Column(type:"boolean", options:["default"=>1])]
    private bool $ivaApplied;

    #[ORM\Column(type:"float", nullable:true)]
    private ?float $price;


    public function getAppointment(): Appointment
    {
        return $this->appointment;
    }

    /**
     * @param mixed $appointment
     * @return AppointmentHasService
     */
    public function setAppointment(Appointment $appointment):self
    {
        $this->appointment = $appointment;
        return $this;
    }


    public function getIvaApplied(): bool
    {
        return $this->ivaApplied;
    }

    /**
     * @param mixed $ivaApplied
     * @return AppointmentHasService
     */
    public function setIvaApplied(?bool $ivaApplied):self
    {
        $this->ivaApplied = $ivaApplied;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService(): Service
    {
        return $this->service;
    }

    public function getIva(): ?float
    {
        return $this->iva;
    }

    /**
     * @param float|null $iva
     * @return AppointmentHasService
     */
    public function setIva(?float $iva): self
    {
        $this->iva = $iva;
        return $this;
    }

    /**
     * @param mixed $service
     * @return AppointmentHasService
     */
    public function setService(Service $service):self
    {
        $this->service = $service;
        return $this;
    }


    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return AppointmentHasService
     */
    public function setPrice(?float $price):self
    {
        $this->price = $price;
        return $this;
    }


    public function getTotalPrice(bool $iva = false): float|int|null
    {
        if($iva){
            if($this->getIvaApplied()){
                return $this->getPrice();
            }else{
                $ivaType = 1 + floatval($this->getIva())/100;
                return $this->getPrice() * $ivaType;
            }

        }else{
            if($this->getIvaApplied()){
                $ivaType = 1 + floatval($this->getIva())/100;
                return $this->getPrice() / $ivaType;
            }else{
                return $this->getPrice();
            }
        }
    }



}
