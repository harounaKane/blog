<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom')
            ->add('nom')
            ->add('ville')
            ->add('email')
            ->add('password')
            ->add('roles', ChoiceType::class, [
                "choices" => [
                    "USER"  => "ROLE_USER",
                    "ADMIN" => "ROLE_ADMIN"
                ],
                "multiple" => true,
                "expanded" => true,
            ])
            ->add('imageVich', VichFileType::class, [
                "label" => "Votre avatar", 
                "required" => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
