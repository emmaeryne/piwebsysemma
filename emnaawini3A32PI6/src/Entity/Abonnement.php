<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AbonnementRepository::class)]
class Abonnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Le service est obligatoire.")]
    private ?Service $service = null;

    #[ORM\ManyToOne(targetEntity: TypeAbonnement::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Le type d'abonnement est obligatoire.")]
    private ?TypeAbonnement $typeAbonnement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    #[Assert\LessThanOrEqual(
        propertyPath: "dateFin",
        message: "La date de début doit être antérieure ou égale à la date de fin."
    )]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire.")]
    #[Assert\GreaterThanOrEqual(
        propertyPath: "dateDebut",
        message: "La date de fin doit être postérieure ou égale à la date de début."
    )]
    private ?\DateTime $dateFin = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le statut actif doit être défini (oui ou non).")]
    private ?bool $estActif = true;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\PositiveOrZero(message: "Le prix total ne peut pas être négatif.")]
    #[Assert\Regex(
        pattern: "/^\d+(\.\d{1,2})?$/",
        message: "Le prix total doit être un nombre valide avec jusqu'à 2 décimales (ex. 123.45).",
        match: true
    )]
    private ?string $prixTotal = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Vous devez indiquer si l'abonnement est gratuit.")]
    private ?bool $estGratuit = false;

    #[ORM\Column(type: Types::STRING, enumType: Statut::class)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private ?Statut $statut = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le nombre de séances restantes doit être défini.")]
    #[Assert\PositiveOrZero(message: "Le nombre de séances restantes ne peut pas être négatif.")]
    private ?int $nombreSeancesRestantes = 0;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'auto-renouvellement doit être défini (oui ou non).")]
    private ?bool $autoRenouvellement = false;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "La durée doit être un nombre positif de mois.")]
    private ?int $dureeMois = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(
        choices: ["Carte bancaire", "Espèces"],
        message: "Le mode de paiement doit être 'Carte bancaire' ou 'Espèces'."
    )]
    #[Assert\Length(
        max: 50,
        maxMessage: "Le mode de paiement ne peut pas dépasser 50 caractères."
    )]
    private ?string $modePaiement = null;

   
    // Getters et Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;
        return $this;
    }

    public function getTypeAbonnement(): ?TypeAbonnement
    {
        return $this->typeAbonnement;
    }

    public function setTypeAbonnement(?TypeAbonnement $typeAbonnement): static
    {
        $this->typeAbonnement = $typeAbonnement;
        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTime|string $dateDebut): static
    {
        if (is_string($dateDebut)) {
            $date = \DateTime::createFromFormat('d/m/Y', $dateDebut);
            if ($date === false) {
                throw new \InvalidArgumentException("La date de début doit être une date valide au format JJ/MM/AAAA.");
            }
            $this->dateDebut = $date;
        } else {
            $this->dateDebut = $dateDebut;
        }
        $this->updateDateFin();
        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTime|string $dateFin): static
    {
        if (is_string($dateFin)) {
            $date = \DateTime::createFromFormat('d/m/Y', $dateFin);
            if ($date === false) {
                throw new \InvalidArgumentException("La date de fin doit être une date valide au format JJ/MM/AAAA.");
            }
            $this->dateFin = $date;
        } else {
            $this->dateFin = $dateFin;
        }
        return $this;
    }

    public function isEstActif(): ?bool
    {
        return $this->estActif;
    }

    public function setEstActif(bool $estActif): static
    {
        $this->estActif = $estActif;
        return $this;
    }

    public function getPrixTotal(): ?string
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(string|float|int|null $prixTotal): static
    {
        if ($this->isEstGratuit()) {
            $this->prixTotal = '0.00'; // Forcer à 0 si gratuit
        } else {
            if (is_numeric($prixTotal)) {
                $this->prixTotal = number_format((float)$prixTotal, 2, '.', '');
            } elseif (is_string($prixTotal) && preg_match("/^\d+(\.\d{1,2})?$/", $prixTotal)) {
                $this->prixTotal = $prixTotal;
            } else {
                $this->prixTotal = null;
            }
        }
        return $this;
    }

    public function isEstGratuit(): ?bool
    {
        return $this->estGratuit;
    }

    public function setEstGratuit(bool $estGratuit): static
    {
        $this->estGratuit = $estGratuit;
        if ($estGratuit) {
            $this->prixTotal = '0.00'; // Forcer à 0 si gratuit
        }
        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(Statut $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getNombreSeancesRestantes(): ?int
    {
        return $this->nombreSeancesRestantes;
    }

    public function setNombreSeancesRestantes(int $nombreSeancesRestantes): static
    {
        $this->nombreSeancesRestantes = $nombreSeancesRestantes;
        return $this;
    }

    public function isAutoRenouvellement(): ?bool
    {
        return $this->autoRenouvellement;
    }

    public function setAutoRenouvellement(bool $autoRenouvellement): static
    {
        $this->autoRenouvellement = $autoRenouvellement;
        $this->updateDateFin();
        return $this;
    }

    public function getDureeMois(): ?int
    {
        return $this->dureeMois;
    }

    public function setDureeMois(?int $dureeMois): static
    {
        $this->dureeMois = $dureeMois;
        $this->updateDateFin();
        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->modePaiement;
    }

    public function setModePaiement(?string $modePaiement): static
    {
        $this->modePaiement = $modePaiement;
        return $this;
    }

    
    #[Assert\Callback]
    public function validatePrixTotal(ExecutionContextInterface $context): void
    {
        if (!$this->isEstGratuit() && $this->prixTotal === null) {
            $context->buildViolation("Le prix total est obligatoire si l'abonnement n'est pas gratuit.")
                ->atPath('prixTotal')
                ->addViolation();
        }
        if ($this->isEstGratuit() && $this->prixTotal !== '0.00') {
            $context->buildViolation("Le prix total doit être 0.00 si l'abonnement est gratuit.")
                ->atPath('prixTotal')
                ->addViolation();
        }
    }

    #[Assert\Callback]
    public function validateDureeMois(ExecutionContextInterface $context): void
    {
        if (!$this->isAutoRenouvellement() && $this->dureeMois === null) {
            $context->buildViolation("La durée en mois est obligatoire si l'auto-renouvellement est désactivé.")
                ->atPath('dureeMois')
                ->addViolation();
        }
    }

    private function updateDateFin(): void
    {
        if ($this->dateDebut === null || !$this->dateDebut instanceof \DateTime) {
            return;
        }

        if ($this->isAutoRenouvellement()) {
            // Si auto-renouvellement est activé, on peut définir une date de fin lointaine
            if ($this->dateFin === null) {
                $this->dateFin = (clone $this->dateDebut)->modify('+1 year');
            }
        } else {
            // Si auto-renouvellement est désactivé, calculer dateFin en fonction de dureeMois
            if ($this->dureeMois !== null && $this->dureeMois > 0) {
                $this->dateFin = (clone $this->dateDebut)->modify("+$this->dureeMois months");
            }
        }
    }

    public function prolongerAbonnement(): void
    {
        if ($this->isAutoRenouvellement()) {
            // Prolonger d'un mois à partir de la date actuelle ou de dateFin
            $now = new \DateTime();
            $this->dateFin = (clone $now)->modify('+1 month');
        }
    }
}