<?php

namespace App\Form;


use App\Entity\Appointment\Appointment;
use App\Entity\Area\Area;
use App\Entity\Center\Center;
use App\Entity\Client\Client;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('client', EntityType::class, [
                // looks for choices from this entity
                'class' => Client::class,
                'choice_label' => function (Client $client) {
                    return $client->getName().' '.$client->getSurnames();
                },
                'multiple' => false,
                'label' => 'Paciente',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])

            ->add('area', EntityType::class, [
                // looks for choices from this entity
                'class' => Area::class,
                'choice_label' => function (Area $area) {
                    return $area->getName();
                },
                'multiple' => false,
                'label' => 'Area de mentorización',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('center', EntityType::class, [
                // looks for choices from this entity
                'class' => Center::class,
                'choice_label' => function (Center $center) {
                    return $center->getName();
                },
                'multiple' => false,
                'label' => 'Centros',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
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
            ->add('time_from', DateTimeType::class, [
                'placeholder' => 'Selecciona una fecha y hora',
            ])
            ->add('modality',null)

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
