<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class,[
                'attr' => [
                    'class' => 'form-control',
                    'label' => 'Guest email',
                ],
            ])
        ;
    }
}
