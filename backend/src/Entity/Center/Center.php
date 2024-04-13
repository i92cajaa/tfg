<?php

namespace App\Entity\Center;

use App\Entity\Area\Area;
use App\Entity\Lesson\Lesson;
use App\Entity\Document\Document;
use App\Entity\Room\Room;
use App\Entity\User\User;
use App\Repository\CenterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CenterRepository::class)]
class Center
{

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $logo = null;

    #[ORM\OneToMany(mappedBy:"center", targetEntity: User::class, cascade:["persist", "remove"])]
    private array|Collection $users;

    #[ORM\ManyToOne(targetEntity: Area::class, inversedBy: 'centers')]
    #[ORM\JoinColumn(name: "area_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Area $area;

    #[ORM\OneToMany(mappedBy:"center", targetEntity: Lesson::class, cascade:["persist", "remove"])]
    private array|Collection $lessons;

    #[ORM\OneToMany(mappedBy:"rooms", targetEntity: Room::class, cascade:["persist", "remove"])]
    private array|Collection $rooms;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name:"name", type:"string", length:255, nullable:false)]
    private string $name;

    #[ORM\Column(name:"address", type:"string", length:255, nullable:false)]
    private string $address;

    #[ORM\Column(name:"phone", type:"string", length:255, nullable:false)]
    private string $phone;

    #[ORM\Column(name:"color", type: 'string', length:255, nullable: false)]
    private string $color;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->lessons = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Document|null
     */
    public function getLogo(): ?Document
    {
        return $this->logo;
    }

    /**
     * @return array|Collection
     */
    public function getUsers(): array|Collection
    {
        return $this->users;
    }

    /**
     * @return Area
     */
    public function getArea(): Area
    {
        return $this->area;
    }

    /**
     * @return array|Collection
     */
    public function getLessons(): array|Collection
    {
        return $this->lessons;
    }

    /**
     * @return array|Collection
     */
    public function getRooms(): array|Collection
    {
        return $this->rooms;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): Center
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param Document $logo
     * @return $this
     */
    public function setLogo(Document $logo): Center
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * @param array|Collection $users
     * @return $this
     */
    public function setUsers(array|Collection $users): Center
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @param Area $area
     * @return $this
     */
    public function setArea(Area $area): Center
    {
        $this->area = $area;
        return $this;
    }

    /**
     * @param array|Collection $lessons
     * @return $this
     */
    public function setLessons(array|Collection $lessons): Center
    {
        $this->lessons = $lessons;
        return $this;
    }

    /**
     * @param array|Collection $rooms
     * @return $this
     */
    public function setRooms(array|Collection $rooms): Center
    {
        $this->rooms = $rooms;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Center
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $address
     * @return $this
     */
    public function setAddress(string $address): Center
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone): Center
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @param string $color
     * @return $this
     */
    public function setColor(string $color): Center
    {
        $this->color = $color;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD AN USER TO THIS CENTER
     * ES: FUNCIÓN PARA AÑADIR UN USUARIO AL CENTRO
     *
     * @param User $user
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addUser(User $user): Center
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE AN USER FROM THIS CENTER
     * ES: FUNCIÓN PARA BORRAR UN USUARIO DEL CENTRO
     *
     * @param User $user
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeUser(User $user): Center
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD A LESSON TO THIS CENTER
     * ES: FUNCIÓN PARA AÑADIR UNA CLASE AL CENTRO
     *
     * @param Lesson $lesson
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addLesson(Lesson $lesson): Center
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE A LESSON FROM THIS CENTER
     * ES: FUNCIÓN PARA BORRAR UNA CLASE DEL CENTRO
     *
     * @param Lesson $lesson
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeLesson(Lesson $lesson): Center
    {
        if ($this->lessons->contains($lesson)) {
            $this->lessons->removeElement($lesson);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD A ROOM TO THIS CENTER
     * ES: FUNCIÓN PARA AÑADIR UNA HABITACIÓN AL CENTRO
     *
     * @param Room $room
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addRoom(Room $room): Center
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE A ROOM FROM THIS CENTER
     * ES: FUNCIÓN PARA BORRAR UNA HABITACIÓN DEL CENTRO
     *
     * @param Room $room
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeRoom(Room $room): Center
    {
        if ($this->rooms->contains($room)) {
            $this->rooms->removeElement($room);
        }

        return $this;
    }
    // ----------------------------------------------------------------
}
