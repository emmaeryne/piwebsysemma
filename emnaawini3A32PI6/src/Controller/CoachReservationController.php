<?php
namespace App\Controller;

use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag; // Add this import
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Route('/coach')]
class CoachReservationController extends AbstractController
{
    private $reservationRepository;

    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    #[Route('/reservations', name: 'app_coach_reservations', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $keyword = $request->query->get('search', '');
        $reservations = $this->getReservationData($keyword);

        return $this->render('reservationcoach/reservations.html.twig', [
            'reservations' => $reservations,
            'search' => $keyword,
            'feedback' => $keyword ? "Résultats filtrés pour : $keyword" : "Liste des réservations chargée.",
        ]);
    }

    #[Route('/reservations/refresh', name: 'app_coach_reservations_refresh', methods: ['GET'])]
    public function refresh(): Response
    {
        $reservations = $this->getReservationData('');
        return $this->redirectToRoute('app_coach_reservations', ['feedback' => 'Données actualisées.']);
    }

    #[Route('/reservations/export', name: 'app_coach_reservations_export', methods: ['GET'])]
    public function export(): Response
    {
        $reservations = $this->getReservationData('');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Réservations Coach');

        // Headers
        $sheet->setCellValue('A1', 'Nom de l\'Abonnement');
        $sheet->setCellValue('B1', 'Nombre de Clients');

        // Data
        $row = 2;
        foreach ($reservations as $reservation) {
            $sheet->setCellValue("A$row", $reservation['subscriptionName']);
            $sheet->setCellValue("B$row", $reservation['clientCount']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'reservations_coach.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
    }

    private function getReservationData(string $keyword = ''): array
    {
        $allReservations = $this->reservationRepository->findAll();
        $subscriptionCounts = [];

        // Count clients per subscription type
        foreach ($allReservations as $reservation) {
            $subscriptionName = $reservation->getTypeAbonnement()->getNom();
            $subscriptionCounts[$subscriptionName] = ($subscriptionCounts[$subscriptionName] ?? 0) + 1;
        }

        $data = [];
        foreach ($subscriptionCounts as $name => $count) {
            if (empty($keyword) || stripos($name, $keyword) !== false) {
                $data[] = [
                    'subscriptionName' => $name,
                    'clientCount' => $count,
                ];
            }
        }

        return $data;
    }
}