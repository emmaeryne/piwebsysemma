<?php

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Psr\Log\LoggerInterface;
use Twilio\Rest\Client;

#[Route('/client')]
class ClientServiceController extends AbstractController
{
    private ServiceRepository $serviceRepository;
    private LoggerInterface $logger;
    private const GIPHY_API_KEY = 'pHZLzVsG2AGE9SUHXXa0bd8dnrhGoPcB';
    private const GIPHY_BASE_URL = 'https://api.giphy.com/v1/gifs/search';
    private const WEATHER_API_KEY = '7a41789a616812043fb138c81cc893aa';
    private const WEATHER_BASE_URL = 'https://api.openweathermap.org/data/2.5/weather';
    private const TWILIO_ACCOUNT_SID = 'AC9ee4ac8e139bf2eebe288428e78ad967';
    private const TWILIO_AUTH_TOKEN = '3bf285de19ceb88b10218de82da7be4e';
    private const TWILIO_NUMBER = '+17403325976';

    public function __construct(
        ServiceRepository $serviceRepository,
        LoggerInterface $logger
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->logger = $logger;
    }

    #[Route('/services', name: 'client_services', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $services = $this->serviceRepository->findAll();
        $selectedService = null;
        $weatherData = null;
        $gifUrl = null;

        if ($request->isMethod('POST')) {
            $serviceId = $request->request->get('service_id');
            $note = (float)$request->request->get('note', 0);

            if ($serviceId) {
                $selectedService = $this->serviceRepository->find($serviceId);

                if ($selectedService) {
                    $selectedService->setNote($note);
                    $this->sendConfirmationSms($selectedService);
                    $gifUrl = $this->searchGif($selectedService->getNom()) ?? $this->getDefaultGif();

                    $this->addFlash('success', sprintf('Service "%s" réservé avec succès !', $selectedService->getNom()));
                } else {
                    $this->addFlash('error', 'Service non trouvé.');
                }
            }
        }

        if ($request->query->has('get_weather')) {
            $weatherData = $this->getWeatherData('Tunis');
            if (!$weatherData) {
                $this->addFlash('error', 'Impossible d\'obtenir les données météorologiques.');
            }
        }

        return $this->render('client_service/index.html.twig', [
            'services' => $services,
            'selectedService' => $selectedService,
            'weatherData' => $weatherData,
            'gifUrl' => $gifUrl,
        ]);
    }

    #[Route('/services/weather', name: 'client_services_weather', methods: ['GET'])]
    public function getWeatherAjax(): JsonResponse
    {
        $weatherData = $this->getWeatherData('Tunis');
        if ($weatherData) {
            return $this->json([
                'temperature' => $weatherData['main']['temp'],
                'conditions' => $weatherData['weather'][0]['description'],
                'icon' => $weatherData['weather'][0]['icon'],
            ]);
        }
        return $this->json(['error' => 'Erreur lors de la récupération des données météo'], 500);
    }

    private function sendConfirmationSms(Service $service): void
    {
        $messageBody = sprintf('Votre réservation pour %s a été confirmée.', $service->getNom());
        try {
            $twilio = new Client(self::TWILIO_ACCOUNT_SID, self::TWILIO_AUTH_TOKEN);
            $twilio->messages->create(
                '+21627417033', // Numéro de destination
                [
                    'from' => self::TWILIO_NUMBER,
                    'body' => $messageBody
                ]
            );
            $this->addFlash('info', 'SMS de confirmation envoyé.');
        } catch (\Exception $e) {
            $this->logger->error('Erreur Twilio API: ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de l\'envoi du SMS : ' . $e->getMessage());
        }
    }

    private function searchGif(string $query): ?string
    {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', self::GIPHY_BASE_URL, [
                'query' => [
                    'api_key' => self::GIPHY_API_KEY,
                    'q' => $query,
                    'limit' => 1,
                    'rating' => 'g',
                    'lang' => 'en'
                ]
            ]);

            $data = $response->toArray();
            if (!empty($data['data'])) {
                return $data['data'][0]['images']['original']['url'];
            }
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Erreur Giphy API: ' . $e->getMessage());
            return null;
        }
    }

    private function getDefaultGif(): string
    {
        return 'https://media.giphy.com/media/3oEjI6SIIHBdRxXI40/giphy.gif';
    }

    private function getWeatherData(string $city): ?array
    {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', self::WEATHER_BASE_URL, [
                'query' => [
                    'q' => $city,
                    'appid' => self::WEATHER_API_KEY,
                    'units' => 'metric',
                    'lang' => 'fr'
                ]
            ]);

            $data = $response->toArray();
            $this->logger->info('Weather API response: ' . json_encode($data));
            return $data;
        } catch (\Exception $e) {
            $this->logger->error('Erreur Weather API: ' . $e->getMessage());
            return null;
        }
    }
}