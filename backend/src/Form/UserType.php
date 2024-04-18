<?php

namespace App\Form;

use App\Entity\Center\Center;
use App\Entity\Role\Role;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('name',TextType::class,[
                'label' => 'Nombre',
                'required' => true
            ])
            ->add('surnames',TextType::class,[
                'label' => 'Apellidos',
                'required' => true
            ])
            ->add('email',TextType::class,[
                'label' => 'Correo Electrónico',
                'required' => true
            ])
            ->add('phone',null,[
                'label' => 'Teléfono',
                'required' => false
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => function (Role $role) {
                    return $role->getName();
                },
                'mapped' => false,
                'multiple' => false,
                'label' => 'Rol',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('center', EntityType::class, [
                'class' => Center::class,
                'choice_label' => function (Center $center) {
                    return $center->getName();
                },
                'multiple' => false,
                'label' => 'Centro',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('img_profile', FileType::class,[
                'mapped' => false,
                'multiple' => false,
                'required' => true,
                'label' => 'Imagen de Perfil',
                'constraints' => [
                    new File([
                        'maxSize' => '9M',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Por favor, carga una imagen válida.',
                    ]),
                ],
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
