<?php

namespace App\Form;

use App\Entity\Lesson\Lesson;
use App\Entity\Room\Room;
use App\Entity\Schedule\Schedule;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teacher', EntityType::class, [
                // looks for choices from this entity
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName().' '.$user->getSurnames();
                },
                'multiple' => false,
                'label' => 'Profesor',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('lesson', EntityType::class, [
                // looks for choices from this entity
                'class' => Lesson::class,
                'choice_label' => function (Lesson $lesson) {
                    return $lesson->getName();
                },
                'multiple' => false,
                'label' => 'Clase',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('room', EntityType::class, [
                // looks for choices from this entity
                'class' => Room::class,
                'choice_label' => function (Room $room) {
                    return 'Planta ' . $room->getFloor() . 'ª - Número ' . $room->getNumber();
                },
                'multiple' => false,
                'label' => 'Habitación',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('date_from', DateTimeType::class, [
                'placeholder' => 'Selecciona una fecha y hora',
            ])
            ->add('date_to', DateTimeType::class, [
                'placeholder' => 'Selecciona una fecha y hora',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Schedule::class,
        ]);
    }
}
