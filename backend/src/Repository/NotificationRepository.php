<?php

namespace App\Repository;

use App\Entity\Document\Document;
use App\Entity\Notification\Notification;
use App\Entity\User\User;
use App\Shared\Traits\DoctrineStorableObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    use DoctrineStorableObject;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }


    public function createNotification(
        string $messageText,
        ?string $link,
        User $user
    ): Notification
    {
        $notification = (new Notification())
            ->setUser($user)
            ->setLink($link)
            ->setMessage($messageText);


        $this->save($this->_em, $notification);

        return $notification;
    }

    public function checkNotification(
        Notification $notification
    ): Notification
    {
        $notification
            ->setSeen(true);

        $this->save($this->_em, $notification);

        return $notification;
    }

    public function deleteNotification(
        Notification $notification
    ){

        $this->delete($this->_em, $notification);

    }



}
