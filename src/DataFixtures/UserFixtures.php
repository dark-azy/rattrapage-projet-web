<?php

namespace App\DataFixtures;

use App\Entity\Administrateur;
use App\Entity\PiloteDePromotion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création de l'administrateur
        $admin = new Administrateur();
        $admin->setNomAdmin('Admin');
        $admin->setPrenomAdmin('Super');
        $admin->setEmail('admin@interned.fr');
        $admin->setPassword(
            $this->passwordHasher->hashPassword(
                $admin,
                'Admin123!'
            )
        );
        $manager->persist($admin);

        // Création du pilote
        $pilote = new PiloteDePromotion();
        $pilote->setNomPilote('Dupont');
        $pilote->setPrenomPilote('Jean');
        $pilote->setEmail('pilote@interned.fr');
        $pilote->setEmailPilote('pilote@interned.fr');
        $pilote->setPassword(
            $this->passwordHasher->hashPassword(
                $pilote,
                'Pilote123!'
            )
        );
        $manager->persist($pilote);

        $manager->flush();
    }
} 