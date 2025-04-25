<?php

namespace App\EntityListener;

use App\Entity\Adresse;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsEntityListener(event: Events::prePersist, entity: Adresse::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Adresse::class)]
class AdresseListener
{
    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

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

    public function prePersist(Adresse $adresse): void
    {
        $this->updateCoordinates($adresse);
    }

    public function preUpdate(Adresse $adresse): void
    {
        $this->updateCoordinates($adresse);
    }

    private function updateCoordinates(Adresse $adresse): void
    {
        if (!$adresse->getLatitude() || !$adresse->getLongitude()) {
            $coordinates = $this->geocodeAddress(
                $adresse->getRue(),
                $adresse->getVille(),
                $adresse->getPays()
            );

            if ($coordinates) {
                $adresse->setLatitude($coordinates['latitude']);
                $adresse->setLongitude($coordinates['longitude']);
            }
        }
    }
} 