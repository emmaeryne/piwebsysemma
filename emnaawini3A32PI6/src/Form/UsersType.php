<?php

namespace App\Form;

use App\Entity\users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('passwordHash', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => true,
                'mapped' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Compte actif',
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Utilisateur' => 'USER',
                    'Propriétaire' => 'OWNER',
                    'Administrateur' => 'ADMIN',
                    'Coach' => 'COACH',
                ],
                'required' => true,
            ])
            ->add('serviceName', TextType::class, [
                'label' => 'Nom du service',
                'required' => false,
            ])
            ->add('serviceType', TextType::class, [
                'label' => 'Type de service',
                'required' => false,
            ])
            ->add('officialId', TextType::class, [
                'label' => 'ID officiel',
                'required' => false,
            ])
            ->add('documents', TextareaType::class, [
                'label' => 'Documents',
                'required' => false,
            ])
            ->add('specialty', TextType::class, [
                'label' => 'Spécialité',
                'required' => false,
            ])
            ->add('experienceYears', IntegerType::class, [
                'label' => 'Années d\'expérience',
                'required' => false,
            ])
            ->add('certifications', TextType::class, [
                'label' => 'Certifications',
                'required' => false,
            ])
            ->add('securityQuestionId', IntegerType::class, [
                'label' => 'ID Question de sécurité',
                'required' => false,
            ])
            ->add('securityAnswer', TextType::class, [
                'label' => 'Réponse de sécurité',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}