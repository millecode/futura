<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Range;
use App\Entity\Contacts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Votre Nom"
            ])
            ->add('email', EmailType::class, [
                'label' => "Votre email"
            ])
            ->add('phone', IntegerType::class, [
                'label' => "Phone",
            ])
            ->add('sujet', TextType::class, [
                'label' => "Sujet"
            ])
            ->add('message', TextareaType::class, [
                'label' => "Message",
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
            'data_class' => Contacts::class,
        ]);
    }
}
