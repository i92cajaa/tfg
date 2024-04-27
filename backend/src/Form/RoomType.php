<?php

namespace App\Form;

use App\Entity\Center\Center;
use App\Entity\Room\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('center',EntityType::class,[
                'class' => Center::class,
                'choice_label' => function (Center $center) {
                    return $center->getName();
                },
                'multiple' => false,
                'label' => 'Centro',
                'attr' => ['class' => 'form-check mb-2 select2', 'autocomplete' => 'off'],
            ])
            ->add('floor',NumberType::class,[
                'label' => 'Planta',
                'required' => true
            ])
            ->add('number',NumberType::class,[
                'label' => 'Número',
                'required' => true
            ])
            ->add('capacity',NumberType::class,[
                'label' => 'Capacidad',
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
            'allow_extra_fields' => true
        ]);
    }
}