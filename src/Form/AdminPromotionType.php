<?php

namespace App\Form;

use App\Entity\Promotion;
use App\Entity\PiloteDePromotion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminPromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la promotion',
                'attr' => ['class' => 'form-control']
            ])
            ->add('campus', TextType::class, [
                'label' => 'Campus',
                'attr' => ['class' => 'form-control']
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['class' => 'form-control']
            ])
            ->add('pilote', EntityType::class, [
                'class' => PiloteDePromotion::class,
                'choice_label' => function (PiloteDePromotion $pilote) {
                    return $pilote->getNomPilote() . ' ' . $pilote->getPrenomPilote();
                },
                'label' => 'Pilote de promotion',
                'attr' => ['class' => 'form-control'],
                'required' => false,
                'placeholder' => 'Sélectionner un pilote'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
        ]);
    }
} 