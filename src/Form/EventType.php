<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Event name',
                'attr' => [
                    'placeholder' => 'Enter the event name',
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Event description',
                'attr' => [
                    'placeholder' => 'Enter the event description',
                    'class' => 'form-control',
                ],
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Event date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
                'data' => new \DateTime('today'),
            ])
            ->add('location', TextType::class, [
                'label' => 'Event location',
                'attr' => [
                    'placeholder' => 'Enter the event location',
                    'class' => 'form-control',
                ],
            ])
            ->add('maxGuests', IntegerType::class, [
                'label' => 'Max number of guests',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                ],
            ])
            ->add('private', CheckboxType::class, [
                'label' => 'Private',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input ms-2',
                ],
            ])  
            ->add('visible', CheckboxType::class, [
                'label' => 'Visible to everyone',
                'required' => false,
                'data' => true,
                'attr' => [
                    'class' => 'form-check-input ms-2',
                ],
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Price ($)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                ],
            ])
            ->add('type', EntityType::class, [
                'label' => 'Event type',
                'class' => Type::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
