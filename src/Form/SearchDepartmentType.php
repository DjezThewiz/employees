<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchDepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [   //"q" = Mot clé se trouvant dans la db
                'label' => 'Recherche : ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom ou prénom...',
                ],
            ])
            ->add('sortBy', ChoiceType::class, [
                'label' => 'Trier par : ',
                'choices' => [
                    'Nom du département' => 'deptName',
                    'description' => 'description',
                    'adresse' => 'address',
                    'Règlement d\'ordre intérieur' => 'roiUrl',
                ],
                'required' => false,
            ])
            ->add('sortOrder', ChoiceType::class, [
                'label' => 'Ordre de tri : ',
                'choices' => [
                    'Croissant' => 'asc',
                    'Décroissant' => 'desc',
                ],
                'required' => false,
            ])
            ->add('rechercher', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Aucune classe de données associée
        ]);
    }
}