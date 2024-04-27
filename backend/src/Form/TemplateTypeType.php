<?php

namespace App\Form;


use App\Entity\Template\TemplateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('active', CheckboxType::class, [
                'required' => false
            ])
            ->add('entity', ChoiceType::class, [
                'choices'  => TemplateType::ENTITIES,
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
                'label' => 'Tipo',
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TemplateType::class,
            'allow_extra_fields' => true
        ]);
    }
}
