<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    private $entityManager;
    private $reservationRepository;
    private $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->reservationRepository = $reservationRepository;
        $this->translator = $translator;
    }

    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(): Response
    {
        $reservations = $this->reservationRepository->findAll();
        $reservationCount = count($reservations);

        // Calculate count of reservations per subscription type
        $typeCounts = [];
        foreach ($reservations as $reservation) {
            $type = $reservation->getTypeAbonnement();
            $typeName = $type ? $type->getNom() : 'N/A';
            $typeCounts[$typeName] = ($typeCounts[$typeName] ?? 0) + 1;
        }

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
            'reservation_count' => $reservationCount,
            'type_counts' => $typeCounts, // Pass the counts to the template
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('reservation.updated'));
            return $this->redirectToRoute('app_reservation_index');
        }

        return $this->render('reservation/edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($reservation);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('deleted'));
        }

        return $this->redirectToRoute('app_reservation_index');
    }
}