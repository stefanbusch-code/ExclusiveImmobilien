<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Location;
use App\Entity\Property;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('property_title', TextareaType::class,[
                'label'=>'Titel',
                'attr' => [
                    'placeholder' => 'Titel eingeben...',
                    'rows'=> 5,
                ]
            ])
            ->add('property_discription', TextareaType::class,[
                'label'=>'Beschreibung',
                'attr' => [
                    'placeholder' => 'Beschreibung eingeben...',
                    'rows'=> 5,
                ]
            ])
            ->add('preis', TextType::class,[
                'label'=>'Preis',
                'attr' => [
                    'placeholder' => 'Preis eingeben...'
                ]
            ])
            ->add('location', LocationType::class)
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'label' => 'Kategorie',
                'placeholder' => 'Kategorie auswählen...'
            ])
            ->add('bild', FileType::class, [
                'label'=>'Bild',
                'mapped'=> true,
                'required'=> false,
                'data_class'=> null,
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Property::class,
        ]);
    }
}