<?php

namespace App\Form;

use App\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Vorname',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Vorname eingeben...',
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nachname',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nachname eingeben...',
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telefon',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Telefonnummer eingeben...',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}
