<?php

namespace App\Form;

use App\Entity\Area\Area;
use App\Entity\Center\Center;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CenterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('logo', FileType::class,[
                'mapped' => false,
                'multiple' => false,
                'required' => true,
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
            ->add('address',TextType::class,[
                'label' => 'Dirección',
                'required' => true
            ])
            ->add('area',EntityType::class,[
                'class' => Area::class,
                'choice_label' => function (Area $area) {
                    return $area->getName();
                },
                'multiple' => false,
                'label' => 'Area',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('phone',TextType::class,[
                'label' => 'Teléfono',
                'required' => true
            ])
            ->add('opening_time',DateTimeType::class,[
                'placeholder' => 'Selecciona una fecha y hora',
                'required' => true
            ])
            ->add('closing_time',DateTimeType::class,[
                'placeholder' => 'Selecciona una fecha y hora',
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
            'data_class' => Center::class,
            'allow_extra_fields' => true
        ]);
    }
}
