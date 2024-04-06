<?php

namespace App\Form;

use App\Entity\Center\Center;
use App\Entity\Client\Client;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('logo', FileType::class,[
                'mapped' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Logotipo',
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
            ->add('name',TextType::class,[
                'label' => 'Nombre',
                'required' => true
            ])
            ->add('socialName',TextType::class,[
                'label' => 'Razón Social',
                'required' => false
            ])
            ->add('center',EntityType::class, [
                'required' => true,
                'label' => 'Centro',
                'class' => Center::class,
                'choice_label' => function (Center $center) {
                    return $center->getName();
                },
                'multiple' => false
            ])
            ->add('speciality',TextType::class,[
                'label' => 'Sector / Especialidad',
                'required' => true

            ])
            ->add('members',null,[
                'label'=>'Nº de miembros',
                'required' => false
            ])
            ->add('girlMembers',null,[
                'label'=>'Nº de miembros mujeres',
                'required' => false
            ])
            ->add('province',null,[
                'label'=>'Provincia',
                'required' => false
            ])
            ->add('cif',null,[
                'label'=>'CIF',
                'required' => false
            ])
            ->add('announcement',null,[
                'label'=>'Convocatoria',
                'required' => false
            ])
            ->add('description',TextType::class,[
                'required' => true,
            ])
            ->add('supportType',TextType::class,[
                'label'=>'Tipo de ayuda',
                'required' => false,
            ])
            ->add('comment',TextType::class,[
                'label'=>'Comentario',
                'required' => false,
            ])
            ->add('representative',TextType::class,[
                'label' => 'Representante',
                'required' => true
            ])
            ->add('position',TextType::class,[
                'label' => 'Cargo',
                'required' => true
            ])
            ->add('gender',ChoiceType::class,[
                'choices'  => [
                    'Masculino' => 'Masculino',
                    'Femenino' => 'Femenino',
                    'Otro' => 'Otro',
                    'Prefiero no decirlo' => 'Prefiero no decirlo'
                ],
                'label' => 'Sexo',
                'required' => false
            ])
            ->add('age',TextType::class,[
                'label' => 'Edad',
                'required' => false
            ])
            ->add('phone',TextType::class,[
                'label' => 'Teléfono',
                'required' => true
            ])
            ->add('email',TextType::class,[
                'label' => 'Email ',
                'required' => true,
                'constraints'=>[
                    new UserUniqueEmail($options['edit'], 'loose')
                ]
            ])
            ->add('representative2',TextType::class,[
                'label' => 'Representante 2',
                'required' => false
            ])
            ->add('position2',TextType::class,[
                'label' => 'Cargo 2',
                'required' => false
            ])
            ->add('phone2',TextType::class,[

                'label' => 'Teléfono 2',
                'required' => false
            ])
            ->add('email2',TextType::class,[

                'label' => 'Email 2',
                'required' => false
            ])
            ->add('gender2',TextType::class,[
                'label' => 'Sexo',
                'required' => false
            ])
            ->add('age2',TextType::class,[
                'label' => 'Edad',
                'required' => false
            ])
            ->add('representative3',TextType::class,[

                'label' => 'Representante 3',
                'required' => false
            ])
            ->add('position3',TextType::class,[

                'label' => 'Cargo 3',
                'required' => false
            ])
            ->add('phone3',TextType::class,[

                'label' => 'Teléfono 3',
                'required' => false
            ])
            ->add('email3',TextType::class,[

                'label' => 'Email 3',
                'required' => false
            ])
            ->add('gender3',TextType::class,[
                'label' => 'Sexo',
                'required' => false
            ])
            ->add('age3',TextType::class,[
                'label' => 'Edad',
                'required' => false
            ])
            ->add('incorporationYear',TextType::class,[
                'label' => 'Año de constitución',
                'required' => false
            ])
            ->add('newCompany',CheckboxType::class,[
                'label'=>'Empresa Nueva',
                'required'=>false,
                'mapped' => false
            ])
            ->add('digitalStartup',CheckboxType::class,[
                'label'=>'Startup Digital',
                'required'=>false,
                'mapped' => false
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Los campos deben coincidir',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => false,
                'label' => 'Contraseña',
                'first_options'  => ['label' => 'Contraseña'],
                'second_options' => ['label' => 'Repetir Contraseña'],
                'constraints' => [
                    new Length(min: 8, minMessage: "La contraseña debe ser minimo de 8 caracteres."),
                ]
            ])
            ->add('alumni',CheckboxType::class,[
                'label'=>'Proyecto Alumni',
                'required'=>false,
                'mapped' => false
            ])
            ->add('goals', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => [
                    'Constitución' => 1,
                    'Pacto de Socios' => 2,
                    'Equipo' => 3,
                    'Facturación' => 4,
                ],
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
            'edit' => false,
            'allow_extra_fields' => true
        ]);
    }
}
