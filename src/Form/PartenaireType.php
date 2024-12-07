<?php

namespace App\Form;

use App\Entity\Partenaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartenaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du partenaire'
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => "Logo du partenaire",
                "required" => false,
                'allow_delete' => true, // Ajoute une option pour supprimer l'image
                'download_label' => 'Voir l\'image actuelle', // Ajoute un lien pour voir l'image
                'download_uri' => true, // Active l'URL de téléchargement
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Partenaire::class,
        ]);
    }
}
