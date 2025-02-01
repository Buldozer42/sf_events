<?php

namespace App\Form;

use App\Entity\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'label' => null,
                'attr' => [
                    'placeholder' => 'Name of the event',
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('isPrivate', ChoiceType::class, [
                'label' => 'Visibility',
                'choices' => [
                    'Public' => false,
                    'Private' => true,
                ],
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => Type::class,
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('price', ChoiceType::class, [
                'label' => 'Price',
                'choices' => [
                    'Free' => 0,
                    'Less than 10€' => 10,
                    'Less than 20€' => 20,
                    'Less than 50€' => 50,
                ],
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control no-help',
                ],
                'data' => new \DateTime('today'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'type_list' => [],
        ]);
    }
}
