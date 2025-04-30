<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email eingeben...',
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Passwort',
                'attr' => [
                    'placeholder' => 'Passwort eingeben...',
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Vorname',
                'attr' => [
                    'placeholder' => 'Vorname eingeben...',
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nachname',
                'attr' => [
                    'placeholder' => 'Nachname eingeben...',
                ]
            ])
            ->add('street', TextType::class, [
                'label' => 'Strasse',
                'attr' => [
                    'placeholder' => 'Strasse eingeben...',
                ]
            ])
            ->add('streetnumber', TextType::class, [
                'label' => 'Nummer',
                'attr' => [
                    'placeholder' => 'Hausnummer eingeben...',
                ]
            ])
            ->add('zipcode', TextType::class, [
                'label' => 'Postleitzahl',
                'attr' => [
                    'placeholder' => 'Postleitzahl eingeben...',
                ]
            ])
            ->add('state', TextType::class, [
                'label' => 'Land',
                'attr' => [
                    'placeholder' => 'Bundesland eingeben...',
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'Land',
                'attr' => [
                    'placeholder' => 'Land eingeben...',
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
