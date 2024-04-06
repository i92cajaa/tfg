<?php


namespace App\Entity\User;


use App\Entity\Area\Area;
use App\Entity\Role\Role;
use App\Repository\UserHasAreaRepository;
use App\Repository\UserHasRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\UniqueConstraint(name:"area_unique", columns: ["user_id", "area_id"])]
#[ORM\Entity(repositoryClass: UserHasAreaRepository::class)]
class UserHasArea
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, cascade:["persist"], inversedBy: 'areas')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Area::class, cascade:["persist"], inversedBy: 'users')]
    #[ORM\JoinColumn(name: "area_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private Area $area;


    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserHasRole
     */
    public function setUser(User $user): UserHasArea
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Area
     */
    public function getArea(): Area
    {
        return $this->area;
    }

    /**
     * @param Area $area
     */
    public function setArea(Area $area): UserHasArea
    {
        $this->area = $area;
        return $this;
    }



}
