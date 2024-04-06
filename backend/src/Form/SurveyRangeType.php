<?php

namespace App\Form;


use App\Entity\Document\SurveyRange;
use Doctrine\DBAL\Types\BooleanType;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate',DateTimeType::class,[
                'placeholder' => 'Selecciona una fecha',
            ])
            ->add('endDate',DateTimeType::class,[
                'placeholder' => 'Selecciona una fecha',
            ])
            ->add('status', null, [
                'label' => 'Estado'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SurveyRange::class,
            'allow_extra_fields' => true
        ]);
    }
}
