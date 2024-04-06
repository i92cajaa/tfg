<?php

namespace App\Form;

use App\Entity\Center\Center;
use App\Entity\Role\Role;
use App\Entity\Area\Area;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('name',null,[
            ])
            ->add('surnames',null,[
            ])
            ->add('email',null,[
                'required' => true
            ])
            ->add('phone',null,[
                'required' => false
            ])
            ->add('modality',null,[
                'required' => false
            ])
            ->add('roles', EntityType::class, [
                'class' => Role::class,
                'choice_label' => function (Role $role) {
                    return $role->getName();
                },
                'mapped' => false
            ])
            ->add('center', EntityType::class, [
                'class' => Center::class,
                'choice_label' => function (Center $center) {
                    return $center->getName();
                },
            ])
            ->add('areas', EntityType::class, [
                'class' => Area::class,
                'choice_label' => function (Area $area) {
                    return $area->getName();
                },
                'mapped' => false,
                'multiple' => true
            ])
            ->add('img_profile', FileType::class,[
                'mapped' => false,
                'data_class' => null,
                'required' => false
            ])
            ->add('calendar_interval', null,[
                "required" => true
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Los campos deben coincidir',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => false,
                'label' => 'Contraseña',
                'first_options'  => ['label' => 'Contraseña'],
                'second_options' => ['label' => 'Repetir Contraseña'],
                'mapped'=> false,
                'constraints' => [
                    new Length(min: 8, minMessage: "La contraseña debe ser minimo de 8 caracteres."),
                ]
            ])
            ->add('same_page',null,[
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'allow_extra_fields' => true
        ]);
    }
}
