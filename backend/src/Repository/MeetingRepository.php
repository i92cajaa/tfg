<?php

namespace App\Repository;

use App\Entity\Appointment\Appointment;
use App\Entity\Meeting\Meeting;
use App\Service\FilterService;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Traits\DoctrineStorableObject;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Meeting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meeting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Meeting[]    findAll()
 * @method Meeting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingRepository extends ServiceEntityRepository
{

    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meeting::class);
    }

    public function createMeeting(
        Appointment $appointment,
        string $subject,
        string $joinUrl,
        string $joinWebUrl,
        string $meetingCode,
        array $options
    ): Meeting
    {
        $newMeeting = (new Meeting())
            ->setSubject($subject)
            ->setAppointment($appointment)
            ->setJoinUrl($joinUrl)
            ->setJoinWebUrl($joinWebUrl)
            ->setMeetingCode($meetingCode)
            ->setOptions($options)
            ;

        $this->persist($newMeeting);

        $appointment->setMeeting($newMeeting);

        $this->_em->getRepository(Appointment::class)->persist($appointment);

        return $newMeeting;

    }

    public function persist(Meeting $meeting)
    {
        $this->save($this->_em, $meeting);
    }

    public function remove(Meeting $meeting)
    {
        $this->delete($this->_em, $meeting);
    }


}
