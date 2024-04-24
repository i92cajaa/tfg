<?php

namespace App\Form;

use App\Entity\Center\Center;
use App\Entity\Client\Client;
use App\Form\Validator\ClientUniqueDni;
use App\Form\Validator\UserUniqueEmail;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
                'label' => 'Nombre',
                'required' => true
            ])
            ->add('surnames',TextType::class,[
                'label' => 'Apellidos',
                'required' => false
            ])
            ->add('phone',TextType::class,[
                'label' => 'Teléfono',
                'required' => true
            ])
            ->add('email',TextType::class,[
                'label' => 'Email ',
                'required' => false,
            ])
            ->add('dni',TextType::class,[
                'label' => 'DNI',
                'required' => false,
                'constraints'=>[
                    new ClientUniqueDni($options['edit'], 'loose')
                ]
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
                    new Length(min: 8, minMessage: "La contraseña debe ser mínimo de 8 caracteres."),
                ]
            ])
            ->add('same_page',null,[
                'mapped' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
            'edit' => false,
            'allow_extra_fields' => true
        ]);
    }
}
