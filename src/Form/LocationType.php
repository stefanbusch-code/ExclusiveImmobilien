<?php

namespace App\Form;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location_zipcode', TextType::class, [
                'attr' => [
                    'placeholder' => 'Postleitzahl eingeben...',
                ],

            ])

            ->add('location_town', TextType::class, [
                'attr' => [
                    'placeholder' => 'Stadt eingeben...',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Town cannot be empty.',
                    ])
                ]
            ])
            ->add('location_street', TextType::class, [
                'attr' => [
                    'placeholder' => 'Straße eingeben...',
                ],
            ])
            ->add('location_streetnumber', TextType::class, [
                'attr' => [
                    'placeholder' => 'Hausnummer eingeben...',
                ],
            ])
            ->add('region', TextType::class, [
                'attr' => [
                    'placeholder' => 'Bundesland eingeben...',
                ],
            ])
            ->add('country', TextType::class, [
                'attr' => [
                    'placeholder' => 'Land eingeben...',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
