<?php

namespace App\Form;

use App\Entity\Client\Booking;
use App\Entity\Client\Client;
use App\Entity\Schedule\Schedule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client',EntityType::class,[
                'class' => Client::class,
                'choice_label' => function (Client $client) {
                    return $client->getFullName();
                },
                'multiple' => false,
                'label' => 'Cliente',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('schedule',EntityType::class,[
                'class' => Schedule::class,
                'choice_label' => function (Schedule $schedule) {
                    return $schedule->getDateFrom()->format('d/m/Y H:i') . ' - ' . $schedule->getDateTo()->format('H:i');
                },
                'multiple' => false,
                'label' => 'Horario',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
            'allow_extra_fields' => true
        ]);
    }
}
