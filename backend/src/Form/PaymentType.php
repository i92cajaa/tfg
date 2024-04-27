<?php

namespace App\Form;


use App\Entity\Appointment\Appointment;

use App\Entity\Payment\Payment;
use NumberFormatter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('amount', NumberType::class, [
                'scale' => 2,
                'rounding_mode' => NumberFormatter::ROUND_HALFUP
            ])
            ->add('appointment', EntityType::class, [
                // looks for choices from this entity
                'class' => Appointment::class,
                'required' => true
            ])
            ->add('payment_method', null, [
                'required' => true
            ])
            ->add('service', null, [
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
            'allow_extra_fields' => true
        ]);
    }
}
