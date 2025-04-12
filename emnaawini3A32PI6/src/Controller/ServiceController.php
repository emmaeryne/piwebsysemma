<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Knp\Component\Pager\PaginatorInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;

#[Route('/service')]
class ServiceController extends AbstractController
{
    public function __construct(
        private readonly ServiceRepository $serviceRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly CacheInterface $cache
    ) {
    }

    #[Route('/{id}', name: 'app_service_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $service = $this->serviceRepository->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Le service demandé n\'existe pas.');
        }

        // Suppression de tout le code lié au QR Code
        return $this->render('service/show.html.twig', [
            'service' => $service,
        ]);
    }

    // Les autres méthodes restent inchangées...

    #[Route('/', name: 'app_service_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search', '');
        $categorie = $request->query->get('categorie', '');
        $prixMax = $request->query->get('prixMax', '');
        $sortBy = $request->query->get('sortBy', 'nom');
        $sortOrder = strtoupper($request->query->get('sortOrder', 'ASC'));

        $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'ASC';
        $prixMaxFloat = $prixMax !== '' ? (float)$prixMax : null;

        $allServices = $this->serviceRepository->findAll();
        $statistics = $this->calculateAdvancedStatistics($allServices);
        $trends = $this->predictServiceTrends($allServices);

        $query = $this->serviceRepository->findByFilters(
            $search,
            $categorie,
            $prixMaxFloat,
            $sortBy,
            $sortOrder,
            true
        );

        $services = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('service/index.html.twig', [
            'services' => $services,
            'search' => $search,
            'categorie' => $categorie,
            'prixMax' => $prixMax,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'statistics' => $statistics,
            'trends' => $trends,
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_service_pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function generatePdf(int $id, Pdf $pdf): Response
    {
        $service = $this->serviceRepository->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Le service demandé n\'existe pas.');
        }

        try {
            $html = $this->renderView('service/pdf.html.twig', ['service' => $service]);
            return new PdfResponse(
                $pdf->getOutputFromHtml($html),
                sprintf('service_%d.pdf', $service->getId())
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération du PDF');
            return $this->redirectToRoute('app_service_show', ['id' => $service->getId()]);
        }
    }

    

    #[Route('/new', name: 'app_service_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleImageUpload($service, $form);

                if (empty($service->getDescription())) {
                    $service->setDescription($this->generateAIDescription($service));
                }

                $this->entityManager->persist($service);
                $this->entityManager->flush();
                $this->cache->delete('services_*');

                $this->addFlash('success', 'Service ajouté avec succès.');
                return $this->redirectToRoute('app_service_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création du service: ' . $e->getMessage());
            }
        }

        return $this->render('service/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_service_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, int $id): Response
    {
        $service = $this->serviceRepository->find($id);
        if (!$service) {
            throw $this->createNotFoundException('Le service demandé n\'existe pas.');
        }

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handleImageUpload($service, $form);

                $this->entityManager->flush();
                $this->cache->delete('services_*');
                $this->addFlash('success', 'Service modifié avec succès.');
                return $this->redirectToRoute('app_service_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la modification: ' . $e->getMessage());
            }
        }

        return $this->render('service/edit.html.twig', [
            'form' => $form->createView(),
            'current_image' => $service->getImage(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_service_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, int $id): Response
    {
        $service = $this->serviceRepository->find($id);
        if (!$service) {
            throw $this->createNotFoundException('Le service demandé n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('delete' . $service->getId(), $request->request->get('_token'))) {
            try {
                $this->deleteImage($service);
                $this->entityManager->remove($service);
                $this->entityManager->flush();
                $this->cache->delete('services_*');
                $this->addFlash('success', 'Service supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_service_index');
    }

    private function handleImageUpload(Service $service, $form): void
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $this->deleteImage($service);
            $newFilename = uniqid() . '.' . $imageFile->guessExtension();
            $imageFile->move(
                $this->getParameter('images_directory'),
                $newFilename
            );
            $service->setImage($newFilename);
        }
    }

    private function deleteImage(Service $service): void
    {
        $image = $service->getImage();
        if ($image && file_exists($this->getParameter('images_directory') . '/' . $image)) {
            unlink($this->getParameter('images_directory') . '/' . $image);
        }
    }

    private function generateAIDescription(Service $service): string
    {
        $client = HttpClient::create();
        try {
            $response = $client->request('POST', 'https://api.openai.com/v1/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['OPENAI_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'text-davinci-003',
                    'prompt' => sprintf(
                        'Génère une description professionnelle pour un service de %s de niveau %s dans la catégorie %s.',
                        $service->getNom(),
                        $service->getNiveauDifficulte(),
                        $service->getCategorie()
                    ),
                    'max_tokens' => 100,
                    'temperature' => 0.7,
                ],
            ]);

            $data = $response->toArray();
            return trim($data['choices'][0]['text']);
        } catch (\Exception $e) {
            return $this->generateDescription($service);
        }
    }

    private function generateDescription(Service $service): string
    {
        return sprintf(
            'Service de %s adapté au niveau %s dans la catégorie %s. Profitez d\'une expérience unique !',
            $service->getNom() ?? 'non spécifié',
            $service->getNiveauDifficulte() ?? 'non défini',
            $service->getCategorie() ?? 'non catégorisé'
        );
    }

    private function calculateAdvancedStatistics(array $services): array
    {
        if (empty($services)) {
            return [
                'average_price' => 0,
                'median_price' => 0,
                'standard_deviation' => 0,
                'average_utilization_rate' => 0,
                'trend' => 'stable',
                'total_services' => 0,
                'most_popular_category' => 'N/A',
            ];
        }

        $prices = array_filter(array_map(fn($service) => $service->getPrix(), $services));
        $reservations = array_map(fn($service) => $service->getNombreReservations() ?? 0, $services);
        $capacities = array_map(fn($service) => $service->getCapaciteMax() ?? 1, $services);

        $count = count($prices);
        $averagePrice = $count > 0 ? array_sum($prices) / $count : 0;
        
        sort($prices);
        $middle = floor(($count - 1) / 2);
        $medianPrice = $count ? ($count % 2 ? $prices[$middle] : ($prices[$middle] + $prices[$middle + 1]) / 2) : 0;

        $variance = $count > 0 ? array_sum(array_map(fn($price) => pow($price - $averagePrice, 2), $prices)) / $count : 0;
        $standardDeviation = sqrt($variance);

        $utilizationRates = array_map(
            fn($res, $cap) => $cap > 0 ? ($res / $cap) * 100 : 0,
            $reservations,
            $capacities
        );
        $averageUtilizationRate = count($utilizationRates) > 0 ? array_sum($utilizationRates) / count($utilizationRates) : 0;

        return [
            'average_price' => round($averagePrice, 2),
            'median_price' => round($medianPrice, 2),
            'standard_deviation' => round($standardDeviation, 2),
            'average_utilization_rate' => round($averageUtilizationRate, 2),
            'total_services' => count($services),
            'most_popular_category' => $this->getMostPopularCategory($services),
        ];
    }

    private function predictServiceTrends(array $services): array
    {
        return array_map(function ($service) {
            $rate = $this->calculateUtilizationRate($service);
            return [
                'nom' => $service->getNom(),
                'current_rate' => round($rate, 2),
                'trend' => $rate > 75 ? 'croissance' : ($rate < 25 ? 'baisse' : 'stable'),
                'recommended_price_adjustment' => $this->suggestPriceAdjustment($service, $rate),
            ];
        }, $services);
    }

    private function calculateUtilizationRate(Service $service): float
    {
        $reservations = $service->getNombreReservations() ?? 0;
        $capacity = $service->getCapaciteMax() ?? 1;
        return $capacity > 0 ? ($reservations / $capacity) * 100 : 0;
    }

    private function suggestPriceAdjustment(Service $service, float $rate): float
    {
        $currentPrice = $service->getPrix() ?? 0;
        if ($rate > 75) {
            return round($currentPrice * 1.1, 2);
        }
        if ($rate < 25) {
            return round($currentPrice * 0.9, 2);
        }
        return $currentPrice;
    }

    private function getMostPopularCategory(array $services): string
    {
        if (empty($services)) {
            return 'N/A';
        }
        $categories = array_count_values(array_map(fn($service) => $service->getCategorie() ?? 'N/A', $services));
        return array_search(max($categories), $categories) ?: 'N/A';
    }
}