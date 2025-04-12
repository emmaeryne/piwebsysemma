<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Form\AbonnementType;
use App\Repository\AbonnementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/abonnement')]
class AbonnementController extends AbstractController
{
    private $entityManager;
    private $abonnementRepository;

    public function __construct(EntityManagerInterface $entityManager, AbonnementRepository $abonnementRepository)
    {
        $this->entityManager = $entityManager;
        $this->abonnementRepository = $abonnementRepository;
    }

    #[Route('/', name: 'app_abonnement_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $sort = $request->query->get('sort');
        $abonnements = $this->abonnementRepository->findAllSorted($sort);

        return $this->render('abonnement/index.html.twig', [
            'abonnements' => $abonnements,
            'sort' => $sort,
        ]);
    }

    #[Route('/new', name: 'app_abonnement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ValidatorInterface $validator): Response
    {
        $abonnement = new Abonnement();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                $errors = $validator->validate($abonnement);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                } elseif ($form->isValid()) {
                    $this->entityManager->persist($abonnement);
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Abonnement créé avec succès.');
                    return $this->redirectToRoute('app_abonnement_index');
                }
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('abonnement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_abonnement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Abonnement $abonnement, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                $errors = $validator->validate($abonnement);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                } elseif ($form->isValid()) {
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Abonnement modifié avec succès.');
                    return $this->redirectToRoute('app_abonnement_index');
                }
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('abonnement/edit.html.twig', [
            'form' => $form->createView(),
            'abonnement' => $abonnement,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_abonnement_delete', methods: ['POST'])]
    public function delete(Request $request, Abonnement $abonnement): Response
    {
        if ($this->isCsrfTokenValid('delete' . $abonnement->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($abonnement);
            $this->entityManager->flush();
            $this->addFlash('success', 'Abonnement supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_abonnement_index');
    }

    #[Route('/{id}/reservation', name: 'app_abonnement_reservation', methods: ['POST'])]
    public function reservation(Request $request, Abonnement $abonnement): Response
    {
        if ($this->isCsrfTokenValid('reservation' . $abonnement->getId(), $request->request->get('_token'))) {
            if ($abonnement->isAutoRenouvellement()) {
                $abonnement->prolongerAbonnement();
                $this->entityManager->flush();
                $this->addFlash('success', 'Réservation effectuée. Abonnement prolongé automatiquement.');
            } else {
                $now = new \DateTime();
                if ($abonnement->getDateFin() < $now) {
                    $this->addFlash('error', 'L\'abonnement est expiré. Veuillez le renouveler manuellement.');
                } else {
                    $this->addFlash('success', 'Réservation effectuée.');
                }
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_abonnement_index');
    }
}