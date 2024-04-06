<?php

namespace App\Form;

use App\Entity\Schedules;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchedulesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                // looks for choices from this entity
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName().' '.$user->getSurnames();
                },
                'multiple' => false,
                'label' => 'Usuario',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('time_from', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'label' => 'Hora de inicio',
                'attr' => ['class' => 'form-group mb-2', 'autocomplete' => 'off'],
            ])
            ->add('time_to', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'choice',
                'label' => 'Hora de fin',
                'attr' => ['class' => 'mb-2', 'autocomplete' => 'off'],
            ])
            ->add('week_day', ChoiceType::class, [
                'choices'  => [
                    'Lunes' => 1,
                    'Martes' => 2,
                    'Miércoles' => 3,
                    'Jueves' => 4,
                    'Viernes' => 5,
                    'Sábado' => 6,
                    'Domingo' => 0,
                ],
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
                'label' => 'Dia de la semana',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Schedules::class,
        ]);
    }
}
