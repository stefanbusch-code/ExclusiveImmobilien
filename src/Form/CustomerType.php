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
            ->add('phone', TextType::class, [
                'label' => 'Telefon',
                'required'=> false,
                'attr' => [
                    'placeholder' => 'Telefonnummer eingeben...',
                ]
            ])

            ->add('firstname', TextType::class, [
                'label' => 'Vorname',
                'required'=> false,
                'attr' => [
                    'placeholder' => 'Vorname eingeben...',
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nachname',
                'required'=> false,
                'attr' => [
                    'placeholder' => 'Nachname eingeben...',
                ]
            ])
            ->add('street', TextType::class, [
                'label' => 'Strasse',
                'required'=> false,
                'attr' => [
                    'placeholder' => 'Strasse eingeben...',
                ]
            ])
            ->add('streetnumber', TextType::class, [
                'label' => 'Nummer',
                'required'=> false,
                'attr' => [
                    'placeholder' => 'Hausnummer eingeben...',
                ]
            ])
            ->add('zipcode', TextType::class, [
                'label' => 'Postleitzahl',
                'required'=> false,
                'attr' => [
                    'placeholder' => 'Postleitzahl eingeben...',
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ort',
                'required'=> false,
                'attr' => [
                    'placeholder' => 'Ort eingeben...',
                ]
            ])
            ->add('state', TextType::class, [
                'label' => 'Land',
                'required'=> false,
                'attr' => [
                    'placeholder' => 'Bundesland eingeben...',
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'Land',
                'required'=> false,
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
