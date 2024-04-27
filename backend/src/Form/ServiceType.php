<?php

namespace App\Form;


use App\Entity\Service\Division;
use App\Entity\Service\Service;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null,[
                'attr' => ['class' => 'form-control mb-2', 'autocomplete' => 'off'],
                'label' => 'Nombre del servicio',
            ])
            ->add('price', NumberType::class, [
                'attr' => ['class' => 'form-control mb-2',
                    'autocomplete' => 'off',
                    'min' => 0,
                    'step' => '.01'
                    ],
                'label' => 'Precio',

            ])
            ->add('needed_time', NumberType::class, [
                'attr' => ['class' => 'form-control mb-2',
                    'autocomplete' => 'off',
                    'min' => 0,
                    'step' => '1'
                ],
                'label' => 'Duración',

            ])
            ->add('division',EntityType::class, [
                // looks for choices from this entity
                'class' => Division::class,
                'choice_label' => function (Division $division) {
                    return $division->getName();
                },
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
                'label' => 'División',
            ])
            ->add('professionals',EntityType::class, [
                // looks for choices from this entity
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName().' '.$user->getSurnames();
                },
                'multiple' => true,
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
                'label' => 'Usuarios',
                'mapped' => false,
                'required' => false
            ])
            ->add('color', ColorType::class,[
                'attr' => ['class' => 'ml-2 mb-2', 'autocomplete' => 'off'],
                'label' => 'Color',
                'required' => false
            ])
            ->add('iva', NumberType::class, [
                'attr' => ['class' => 'form-control mb-2',
                    'autocomplete' => 'off',
                    'min' => 0,
                    'step' => '0.01'
                ],
                'label' => 'IVA',
                'required' => false
            ])
            ->add('iva_applied', CheckboxType::class, [
                'attr' => ['class' => 'form-control mb-2',
                    'autocomplete' => 'off',
                ],
                'label' => '¿IVA aplicado?',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
            'allow_extra_fields' => true
        ]);
    }
}
