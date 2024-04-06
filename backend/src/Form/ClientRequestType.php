<?php

namespace App\Form;

use App\Entity\Client\Client;
use App\Entity\Payment\PaymentMethod;
use App\Entity\ClientRequest\ClientRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',null,[
                'required' => false
            ])
            ->add('surnames',null,[
            ])
            ->add('email',null,[
                'required' => false
            ])
            ->add('phone',null,[
                'required' => false,
            ])
            ->add('comments', TextareaType::class, [
                'required' => false
            ])
            ->add('locale',null,[
                'required' => false
            ])
            ->add('timezone',null,[
                'required' => false
            ])
            ->add('firstAnswer',TextareaType::class,[
                'required' => true
            ])
            ->add('secondAnswer',TextareaType::class,[
                'required' => true
            ])
            ->add('thirdAnswer',TextareaType::class,[
                'required' => true
            ])
            ->add('fourthAnswer',TextareaType::class,[
                'required' => true
            ])
            ->add('fifthAnswer',TextareaType::class,[
                'required' => true
            ])
            ->add('sixthAnswer',TextareaType::class,[
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClientRequest::class,
            'allow_extra_fields' => true,
        ]);
    }
}
