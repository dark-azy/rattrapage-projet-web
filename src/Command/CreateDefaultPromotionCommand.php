<?php

namespace App\Command;

use App\Entity\Promotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-default-promotion',
    description: 'Creates the default promotion',
)]
class CreateDefaultPromotionCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $promotion = new Promotion();
        $promotion->setNom('Promotion par défaut');
        $promotion->setCampus('Nancy');
        $promotion->setAdresse('Forêt de Haye');

        $this->entityManager->persist($promotion);
        $this->entityManager->flush();

        $io->success('La promotion par défaut a été créée avec succès.');

        return Command::SUCCESS;
    }
} 