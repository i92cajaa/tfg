<?php

namespace App\Form;

use App\Entity\Center\Center;
use App\Entity\Lesson\Lesson;
use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class LessonType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', FileType::class,[
                'mapped' => false,
                'multiple' => false,
                'required' => true,
                'label' => 'Imagen',
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
            ->add('duration',NumberType::class,[
                'label' => 'Duración',
                'scale' => 1,
                'required' => true
            ])
            ->add('description',TextType::class,[
                'label' => 'Descripción',
                'required' => false
            ])
            ->add('center',EntityType::class,[
                'class' => Center::class,
                'choice_label' => function (Center $center) {
                    return $center->getName();
                },
                'multiple' => false,
                'label' => 'Centro',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName().' '.$user->getSurnames();
                },
                'mapped' => false,
                'multiple' => true,
                'label' => 'Usuarios',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'allow_extra_fields' => true
        ]);
    }
}