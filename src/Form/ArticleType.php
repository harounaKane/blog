<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Auteur;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description', TextareaType::class)
            ->add("image", FileType::class, [
                "mapped" => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png', 'image/jpeg', 'image/gif'
                        ],
                        'mimeTypesMessage' => 'Uploader une image (png, jpeg, jpg ...)',
                    ])
                ],
            ])
            ->add('auteur', EntityType::class, [
                'class' => Auteur::class,
                'choice_label' => function($aut){
                    return $aut->getPrenom();
                },
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' =>  function($cat){
                    return $cat->getNom();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
