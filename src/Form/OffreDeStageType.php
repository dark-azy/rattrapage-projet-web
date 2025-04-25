<?php

namespace App\Form;

use App\Entity\OffreDeStage;
use App\Entity\Entreprise;
use App\Entity\PiloteDePromotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreDeStageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre_offre', TextType::class, [
                'label' => 'Titre de l\'offre',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description_offre', TextareaType::class, [
                'label' => 'Description de l\'offre',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('competences_requises', TextareaType::class, [
                'label' => 'Compétences requises',
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('duree_stage', IntegerType::class, [
                'label' => 'Durée du stage (en semaines)',
                'attr' => ['class' => 'form-control', 'min' => 1]
            ])
            ->add('date_debut_stage', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('date_fin_stage', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('salaire', MoneyType::class, [
                'label' => 'Salaire mensuel',
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control']
            ])
            ->add('statut_offre', ChoiceType::class, [
                'label' => 'Statut de l\'offre',
                'choices' => [
                    'Disponible' => 'Disponible',
                    'Pourvue' => 'Pourvue',
                    'Expirée' => 'Expirée'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'choice_label' => 'nom',
                'label' => 'Entreprise',
                'attr' => ['class' => 'form-control']
            ])
            ->add('pilote', EntityType::class, [
                'class' => PiloteDePromotion::class,
                'choice_label' => function(PiloteDePromotion $pilote) {
                    return $pilote->getNomPilote() . ' ' . $pilote->getPrenomPilote();
                },
                'label' => 'Pilote responsable',
                'attr' => ['class' => 'form-control'],
                'disabled' => !$options['is_admin'],
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreDeStage::class,
            'is_admin' => false,
        ]);
    }
} 