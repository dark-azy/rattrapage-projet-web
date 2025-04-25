<?php

namespace App\Command;

use App\Entity\PiloteDePromotion;
use App\Entity\Promotion;
use App\Entity\Etudiant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'app:create-users',
    description: 'Creates initial users'
)]
class CreateUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Créer le pilote
        $pilote = new PiloteDePromotion();
        $pilote->setEmail('kamal.alaili@viacesi.fr');
        $pilote->setNomPilote('ALAILI');
        $pilote->setPrenomPilote('Kamal');
        $pilote->setEmailPilote('kamal.alaili@viacesi.fr');
        $hashedPassword = $this->passwordHasher->hashPassword($pilote, 'Cesi24');
        $pilote->setPassword($hashedPassword);
        
        $this->entityManager->persist($pilote);
        
        // Créer la promotion
        $promotion = new Promotion();
        $promotion->setNom('CPI-A2-Info');
        $promotion->setCampus('Nancy');
        $promotion->setAdresse('Forêt de Haye');
        $promotion->setPilote($pilote);
        
        $this->entityManager->persist($promotion);
        
        // Créer l'étudiant
        $etudiant = new Etudiant();
        $etudiant->setEmail('vladimir.machjer@viacesi.fr');
        $etudiant->setNomEtudiant('MACHJER');
        $etudiant->setPrenomEtudiant('Vladimir');
        $hashedPassword = $this->passwordHasher->hashPassword($etudiant, 'Cesi24');
        $etudiant->setPassword($hashedPassword);
        $etudiant->setPromotion($promotion);
        
        $this->entityManager->persist($etudiant);
        
        $this->entityManager->flush();
        
        $output->writeln('Users created successfully!');
        
        return Command::SUCCESS;
    }
} 