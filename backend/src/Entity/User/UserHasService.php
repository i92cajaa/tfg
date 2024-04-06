<?php


namespace App\Entity\User;


use App\Entity\Service\Service;
use App\Repository\UserHasServiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\UniqueConstraint(name:"service_unique", columns: ["user_id", "service_id"])]
#[ORM\Entity(repositoryClass: UserHasServiceRepository::class)]
class UserHasService
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, cascade:["persist"], inversedBy: 'services')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Service::class, cascade:["persist"], inversedBy: 'professionals')]
    #[ORM\JoinColumn(name: "service_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Service $service;


    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserHasService
     */
    public function setUser(User $user): UserHasService
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService(): Service
    {
        return $this->service;
    }

    /**
     * @param Service $service
     * @return UserHasService
     */
    public function setService(Service $service): UserHasService
    {
        $this->service = $service;
        return $this;
    }





}
