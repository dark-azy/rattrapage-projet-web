<?php

namespace App\DataFixtures;

use App\Entity\Promotion;
use App\Entity\PiloteDePromotion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PromotionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Création de la promotion CPI-A2-Informatique
        $promotion = new Promotion();
        $promotion->setNom('CPI-A2-Informatique');
        $promotion->setCampus('Nancy');
        $promotion->setAdresse('2 Rue Jean Lamour, 54500 Vandœuvre-lès-Nancy');

        // Récupération du pilote créé dans UserFixtures
        $piloteRepository = $manager->getRepository(PiloteDePromotion::class);
        $pilote = $piloteRepository->findOneBy(['email' => 'pilote@interned.fr']);
        
        if ($pilote) {
            $promotion->setPilote($pilote);
        }

        $manager->persist($promotion);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
} 
 