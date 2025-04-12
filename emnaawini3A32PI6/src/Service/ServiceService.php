<?php

namespace App\Service;

use App\Entity\Service;

class ServiceService
{
    // Simule une récupération depuis une base de données ou autre source
    public function getAllServices(): array
    {
        // À remplacer par une vraie logique (ex: Doctrine Repository)
        return [
            new Service('Service 1', 50, 'Facile', 30),
            new Service('Service 2', 75, 'Moyen', 60),
        ];
    }

    public function findServiceById(?string $id): ?Service
    {
        // À implémenter avec une vraie logique
        $services = $this->getAllServices();
        foreach ($services as $service) {
            if ($service->getNom() === $id) { // Simulé avec le nom pour l'exemple
                return $service;
            }
        }
        return null;
    }
}