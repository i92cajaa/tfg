<?php

namespace App\Entity\Lesson;

use App\Entity\Center\Center;
use App\Entity\Document\Document;
use App\Entity\Schedule\Schedule;
use App\Entity\User\UserHasLesson;
use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
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
    private Document $image;

    #[ORM\ManyToOne(targetEntity: Center::class, inversedBy: 'lessons')]
    #[ORM\JoinColumn(name: "center_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Center $center;

    #[ORM\OneToMany(mappedBy:"lesson", targetEntity: UserHasLesson::class, cascade:["persist", "remove"])]
    private array|Collection $users;

    #[ORM\OneToMany(mappedBy:"lesson", targetEntity: Schedule::class, cascade:["persist", "remove"])]
    private array|Collection $schedules;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(name:"name", type:"string", length:255, nullable:false)]
    private string $name;

    #[ORM\Column(name:"duration", type:"float", nullable:false)]
    private float $duration;

    #[ORM\Column(name:"description", type:"string", length:255, nullable:true)]
    private ?string $description;

    #[ORM\Column(name:"status", type:"boolean", nullable:false)]
    private bool $status = true;

    #[ORM\Column(name:"color", type:"string", length:255, nullable:false)]
    private string $color;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->schedules = new ArrayCollection();
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
     * @return Document
     */
    public function getImage(): Document
    {
        return $this->image;
    }

    /**
     * @return Center
     */
    public function getCenter(): Center
    {
        return $this->center;
    }

    /**
     * @return array|Collection
     */
    public function getUsers(): array|Collection
    {
        return $this->users;
    }

    /**
     * @return array|Collection
     */
    public function getSchedules(): array|Collection
    {
        return $this->schedules;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
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
     * @param Document $image
     * @return $this
     */
    public function setImage(Document $image): Lesson
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @param Center $center
     * @return $this
     */
    public function setCenter(Center $center): Lesson
    {
        $this->center = $center;
        return $this;
    }

    /**
     * @param array|Collection $users
     * @return $this
     */
    public function setUsers(array|Collection $users): Lesson
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @param array|Collection $schedules
     * @return $this
     */
    public function setSchedules(array|Collection $schedules): Lesson
    {
        $this->schedules = $schedules;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): Lesson
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param float $duration
     * @return $this
     */
    public function setDuration(float $duration): Lesson
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): Lesson
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setStatus(bool $status): Lesson
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string $color
     * @return $this
     */
    public function setColor(string $color): Lesson
    {
        $this->color = $color;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD USER TO LESSON
     * ES: FUNCIÓN PARA AÑADIR USUARIO A CLASE
     *
     * @param UserHasLesson $userHasLesson
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addUser(UserHasLesson $userHasLesson): Lesson
    {
        if (!$this->users->contains($userHasLesson)) {
            $this->users->add($userHasLesson);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE USER FROM LESSON
     * ES: FUNCIÓN PARA BORRAR USUARIO DE CLASE
     *
     * @param UserHasLesson $userHasLesson
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeUser(UserHasLesson $userHasLesson): Lesson
    {
        if ($this->users->contains($userHasLesson)) {
            $this->users->removeElement($userHasLesson);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD SCHEDULE TO LESSON
     * ES: FUNCIÓN PARA AÑADIR HORARIO A CLASE
     *
     * @param Schedule $schedule
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addSchedule(Schedule $schedule): Lesson
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
        }

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE SCHEDULE FROM LESSON
     * ES: FUNCIÓN PARA BORRAR HORARIO DE CLASE
     *
     * @param Schedule $schedule
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removeSchedule(Schedule $schedule): Lesson
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
        }

        return $this;
    }
    // ----------------------------------------------------------------

}