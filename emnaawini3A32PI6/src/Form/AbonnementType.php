<?php

namespace App\Form;

use App\Entity\Abonnement;
use App\Entity\Service;
use App\Entity\Statut;
use App\Entity\TypeAbonnement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbonnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'nom',
                'label' => 'Service',
                'placeholder' => 'Sélectionnez un service',
            ])
            ->add('typeAbonnement', EntityType::class, [
                'class' => TypeAbonnement::class,
                'choice_label' => 'nom',
                'label' => 'Type d\'abonnement',
                'placeholder' => 'Sélectionnez un type',
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => ['placeholder' => 'JJ/MM/AAAA'],
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => ['placeholder' => 'JJ/MM/AAAA', 'readonly' => true],
            ])
            ->add('estActif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('estGratuit', CheckboxType::class, [
                'label' => 'Gratuit',
                'required' => false,
            ])
            ->add('prixTotal', NumberType::class, [
                'label' => 'Prix total (€)',
                'scale' => 2,
                'html5' => false,
                'attr' => [
                    'step' => '0.01',
                    'class' => 'form-control',
                ],
                'data' => $options['data'] ? $options['data']->getPrixTotal() : '0.00',
                'disabled' => $options['data'] ? $options['data']->isEstGratuit() : false,
            ])
            ->add('statut', EnumType::class, [
                'class' => Statut::class,
                'label' => 'Statut',
            ])
            ->add('nombreSeancesRestantes', NumberType::class, [
                'label' => 'Séances restantes',
            ])
            ->add('autoRenouvellement', CheckboxType::class, [
                'label' => 'Auto-renouvellement',
                'required' => false,
            ])
            ->add('dureeMois', NumberType::class, [
                'label' => 'Durée (en mois)',
                'required' => false,
                'attr' => ['min' => 1],
            ])
            ->add('modePaiement', ChoiceType::class, [
                'choices' => [
                    'Carte bancaire' => 'Carte bancaire',
                    'Espèces' => 'Espèces',
                ],
                'label' => 'Mode de paiement',
                'required' => false,
                'placeholder' => 'Sélectionnez un mode',
            ]);
           
          
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Abonnement::class,
        ]);
    }
}