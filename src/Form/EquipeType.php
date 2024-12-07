<?php

namespace App\Form;

use App\Entity\Equipes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Nom"
            ])
            ->add('poste', TextType::class, [
                'label' => "Son Poste actuelle"
            ])
            ->add('email', EmailType::class, [
                'label' => "Email pro"
            ])
            ->add('phone', IntegerType::class, [
                'label' => "Numéro de téléphone"
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => "Image",
                "required" => false,
                'allow_delete' => true, // Ajoute une option pour supprimer l'image
                'download_label' => 'Voir l\'image actuelle', // Ajoute un lien pour voir l'image
                'download_uri' => true, // Active l'URL de téléchargement
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description :",
                'attr' => [
                    'rows' => 5, // Hauteur en nombre de lignes
                    'cols' => 50, // Longueur en nombre de colonnes
                    'style' => 'resize: none;', // Désactive le redimensionnement manuel si nécessaire
                ],

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipes::class,
        ]);
    }
}
