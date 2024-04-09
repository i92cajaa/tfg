<?php

namespace App\Entity\User;

use App\Entity\Class\Lesson;
use App\Repository\UserHasLessonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserHasLessonRepository::class)]
class UserHasLesson
{

    // ----------------------------------------------------------------
    // Primary Keys
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'lessons')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Lesson::class, inversedBy: 'users')]
    #[ORM\JoinColumn(name: "lesson_id", referencedColumnName:"id", nullable:false, onDelete: 'CASCADE')]
    private Lesson $lesson;

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Lesson
     */
    public function getLesson(): Lesson
    {
        return $this->lesson;
    }

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

    /**
     * @param User $user
     * @return UserHasLesson
     */
    public function setUser(User $user): UserHasLesson
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param Lesson $lesson
     * @return UserHasLesson
     */
    public function setLesson(Lesson $lesson): UserHasLesson
    {
        $this->lesson = $lesson;

        return $this;
    }
}