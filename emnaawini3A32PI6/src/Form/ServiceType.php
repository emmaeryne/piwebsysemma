<?php

namespace App\Form;

use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range; // Ajout de la contrainte Range

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'scale' => 2, // Limite à 2 décimales
                'html5' => true, // Utilise l'input HTML5 de type "number"
                'attr' => [
                    'step' => '0.01', // Permet les décimaux
                    'min' => 0, // Valeur minimale dans l'interface
                ],
                'invalid_message' => 'Veuillez entrer un prix valide (nombre positif).',
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 1000, // Ajout d'une limite supérieure raisonnable (ajustez selon vos besoins)
                        'notInRangeMessage' => 'Le prix doit être compris entre {{ min }} et {{ max }}.',
                    ]),
                ],
            ])
            ->add('estActif', ChoiceType::class, [
                'choices' => ['Oui' => true, 'Non' => false],
            ])
            ->add('capaciteMax')
            ->add('categorie')
            ->add('dureeMinutes')
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'Débutant' => 1,
                    'Intermédiaire' => 2,
                    'Avancé' => 3,
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPG, PNG)',
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}