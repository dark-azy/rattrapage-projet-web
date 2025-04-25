<?php

namespace App\Form;

use App\DTO\EntrepriseDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntrepriseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => ['class' => 'form-control']
            ])
            ->add('secteur', ChoiceType::class, [
                'label' => 'Secteur d\'activité',
                'choices' => [
                    'Informatique' => 'Informatique',
                    'Technologie' => 'Technologie',
                    'Finance' => 'Finance',
                    'Ressources humaines' => 'Ressources humaines',
                    'Santé' => 'Santé',
                    'Education' => 'Education',
                    'Industrie' => 'Industrie',
                    'Système embarqué' => 'Système embarqué',
                    'Commerce' => 'Commerce',
                    'Services' => 'Services',
                    'Autre' => 'Autre'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'attr' => ['class' => 'form-control']
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code Postal',
                'attr' => ['class' => 'form-control']
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'attr' => ['class' => 'form-control']
            ])
            ->add('pays', TextType::class, [
                'label' => 'Pays',
                'attr' => ['class' => 'form-control']
            ])
            ->add('active', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => true,
                    'Inactif' => false
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('logo', UrlType::class, [
                'label' => 'Logo (URL)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EntrepriseDTO::class,
        ]);
    }
} 