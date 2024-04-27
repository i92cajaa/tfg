<?php

namespace App\Form;

use App\Entity\Area\Area;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true
            ])
            ->add('city', TextType::class, [
                'label' => 'Ciudad',
                'required' => true
            ])
            ->add('color', TextType::class, [
                'label' => 'Color',
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Area::class,
            'allow_extra_fields' => true
        ]);
    }
}