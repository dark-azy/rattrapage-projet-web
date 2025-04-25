<?php

namespace App\Command;

use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:update-geocoding',
    description: 'Met à jour les coordonnées géographiques de toutes les entreprises',
)]
class UpdateGeocodingCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EntrepriseRepository $entrepriseRepository,
        private HttpClientInterface $httpClient
    ) {
        parent::__construct();
    }

    private function geocodeAddress(string $rue, string $ville, string $pays): ?array
    {
        try {
            $query = http_build_query([
                'street' => $rue,
                'city' => $ville,
                'country' => $pays,
                'format' => 'json'
            ]);

            $response = $this->httpClient->request(
                'GET',
                'https://nominatim.openstreetmap.org/search?' . $query,
                [
                    'headers' => [
                        'User-Agent' => 'Interned/1.0'
                    ]
                ]
            );

            $data = $response->toArray();

            if (!empty($data)) {
                return [
                    'latitude' => (float) $data[0]['lat'],
                    'longitude' => (float) $data[0]['lon']
                ];
            }
        } catch (\Exception $e) {
            // Log l'erreur mais continue sans coordonnées
        }

        return null;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entreprises = $this->entrepriseRepository->findAll();
        $count = 0;

        foreach ($entreprises as $entreprise) {
            $adresse = $entreprise->getAdresse();
            if ($adresse && !$adresse->getLatitude()) {
                $coordinates = $this->geocodeAddress(
                    $adresse->getRue(),
                    $adresse->getVille(),
                    $adresse->getPays()
                );

                if ($coordinates) {
                    $adresse->setLatitude($coordinates['latitude']);
                    $adresse->setLongitude($coordinates['longitude']);
                    $this->entityManager->persist($adresse);
                    $count++;
                }

                // Pause pour respecter les limites de l'API
                sleep(1);
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d adresses ont été mises à jour avec leurs coordonnées géographiques.', $count));

        return Command::SUCCESS;
    }
} 