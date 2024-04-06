<?php

namespace App\Form;

use App\Entity\Center\Center;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CenterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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
            ->add('city',TextType::class,[
                'label' => 'Ciudad',
                'required' => true
            ])
            ->add('phone',TextType::class,[
                'label' => 'Teléfono',
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Center::class,
            'allow_extra_fields' => true
        ]);
    }
}
