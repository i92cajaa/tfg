<?php

namespace App\Form;


use App\Entity\Festive\Festive;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FestiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null,[
                'attr' => ['class' => 'form-control mb-2', 'autocomplete' => 'off'],
                'label' => 'Nombre de la ausencia',
            ])
            ->add('user', EntityType::class, [
                // looks for choices from this entity
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName().' '.$user->getSurnames();
                },
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
                'label' => 'Usuarios',
                'placeholder' => 'Todos',
                'required' => false
            ])
            ->add('date', DateTimeType::class,[
                'attr' => ['class' => 'form-control mb-2', 'autocomplete' => 'off'],
                'label' => 'Fecha de la ausencia',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd-MM-yyyy'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Festive::class,
            'allow_extra_fields' => true
        ]);
    }
}
