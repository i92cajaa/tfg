<?php


namespace App\Entity\User;


use App\Entity\Client\Client;
use App\Entity\Permission\Permission;
use App\Repository\UserHasClientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\UniqueConstraint(name:"client_unique", columns: ["user_id", "client_id"])]
#[ORM\Entity(repositoryClass: UserHasClientRepository::class)]
class UserHasClient
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, cascade:["persist"], inversedBy: 'clients')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Client::class, cascade:["persist"], inversedBy: 'users')]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Client $client;


    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserHasClient
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return UserHasClient
     */
    public function setClient(Client $client): UserHasClient
    {
        $this->client = $client;
        return $this;
    }




}
