<?php

namespace App\Repository;

use App\Entity\Appointment\Appointment;
use App\Entity\Appointment\AppointmentLog;
use App\Entity\User\User;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AppointmentLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppointmentLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppointmentLog[]    findAll()
 * @method AppointmentLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentLogRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppointmentLog::class);
    }

    public function createAppointmentLog(
        ?Appointment $appointment,
        ?User $user,
        string $job,
        ?string $comments
    ): AppointmentLog
    {
        $message = (new AppointmentLog())
            ->setAppointment($appointment)
            ->setWhoChanged($user)
            ->setJobDone($job)
            ->setComments($comments)
        ;

        $this->save($this->_em, $message);

        return $message;
    }


}
