<?php

namespace App\Form;

use App\Entity\ExtraAppointmentField\ExtraAppointmentFieldType;
use App\Entity\Service\Division;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ExtraAppointmentFieldTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null,[
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => ExtraAppointmentFieldType::TYPES,
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
                'label' => 'Tipo',
                'required' => true
            ])
            ->add('position', ChoiceType::class, [
                'choices'  => ExtraAppointmentFieldType::POSITIONS,
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
                'label' => 'Tipo',
                'required' => true
            ])
            ->add('division', EntityType::class, [
                // looks for choices from this entity
                'class' => Division::class,
                'choice_label' => function (Division $division) {
                    return $division->getName();
                },
                'required' => false
            ])
            ->add('description', null,[
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExtraAppointmentFieldType::class,
            'allow_extra_fields' => true
        ]);
    }
}
