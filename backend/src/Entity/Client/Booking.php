<?php

namespace App\Entity\Client;

use App\Entity\Schedule\Schedule;
use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{

    // ----------------------------------------------------------------
    // Primary Keys
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Client $client;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Schedule::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(name: "schedule_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Schedule $schedule;

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
     * @return Schedule
     */
    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client): Booking
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param Schedule $schedule
     * @return $this
     */
    public function setSchedule(Schedule $schedule): Booking
    {
        $this->schedule = $schedule;
        return $this;
    }
}