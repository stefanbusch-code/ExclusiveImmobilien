<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Location;
use App\Entity\Property;
use App\Repository\LocationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyFilterType extends AbstractType
{
    private LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price_min', IntegerType::class,[
                'required'=> false,
                'label'=>'Min. Preis',
                'attr'=>['Placeholder'=>'ab...']
            ])
            ->add('price_max', IntegerType::class,[
                'required'=> false,
                'label'=>'Max. Preis',
                'attr'=>['Placeholder'=>'bis...']
            ])
            ->add('country', ChoiceType::class,[
                'required'=> false,
                'label'=>'Land',
                'choices'=> $this->getCountries(),
                'attr'=>['Placeholder'=>'Land wählen...']
            ])
            ->add('region', ChoiceType::class,[
                'required'=> false,
                'label'=>'Region',
                'choices'=> $this->getRegions(),
                'attr'=>['Placeholder'=>'Region wählen...']
            ])
            ->add('town', ChoiceType::class,[
                'required'=> false,
                'label'=>'Stadt',
                'choices'=> $this->getTowns(),
                'attr'=>['Placeholder'=>'Stadt wählen...']
            ])
            ->add('speichern', SubmitType::class,[
                'label' => 'filtern',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ]);
    }

    public function getCountries()
    {
        $location = $this ->locationRepository -> findAll();
        $countries = [];
        foreach ($location as $location) {
            $countries[$location->getCountry()] = $location->getCountry();
        }
        return array_unique($countries);
    }

    public function getRegions()
    {
        $location = $this ->locationRepository -> findAll();
        $regions = [];
        foreach ($location as $location) {
            $regions[$location->getRegion()] = $location->getRegion();
        }
        return array_unique($regions);
    }

    public function getTowns()
    {
        $location = $this ->locationRepository -> findAll();
        $towns = [];
        foreach ($location as $location) {
            $towns[$location->getTown()] = $location->getTown();
        }
        return array_unique($towns);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
