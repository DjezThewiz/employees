<?php

namespace App\Form;

use App\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Gender;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Department;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('birthDate')
            ->add('firstName')
            ->add('lastName')
            ->add('gender', EnumType::class, ['class' => Gender::class])
            ->add('photo')
            ->add('email')
            ->add('hireDate')
            ->add('isVerified')
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'choice_label' => function (Department $department) {
                    return $department->getId() . ' - ' . $department->getDeptName();
                },
                'multiple' => true, //Un employé peut appartenir à plusieurs départements.
                'expanded' => false,
                'required' => true,
                'by_reference' => false, //Indique à Symfony de manipuler la collection
                'label' => 'Département',
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
