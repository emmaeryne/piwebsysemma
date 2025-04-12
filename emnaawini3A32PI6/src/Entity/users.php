<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @ORM\Table(name="users")
 */
class users implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank
     * @Assert\Length(max=50)
     */
    private ?string $username = null;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\NotBlank
     * @Assert\Email
     * @Assert\Length(max=100)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $passwordHash = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isActive = true;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank
     * @Assert\Choice(choices={"USER", "OWNER", "ADMIN", "COACH"})
     */
    private ?string $role = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $serviceName = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $serviceType = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $officialId = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $documents = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private ?string $specialty = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $experienceYears = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $certifications = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $securityQuestionId = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $securityAnswer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function setPassword(string $password): self
    {
        $this->passwordHash = $password;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function setServiceName(?string $serviceName): self
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    public function getServiceType(): ?string
    {
        return $this->serviceType;
    }

    public function setServiceType(?string $serviceType): self
    {
        $this->serviceType = $serviceType;
        return $this;
    }

    public function getOfficialId(): ?string
    {
        return $this->officialId;
    }

    public function setOfficialId(?string $officialId): self
    {
        $this->officialId = $officialId;
        return $this;
    }

    public function getDocuments(): ?string
    {
        return $this->documents;
    }

    public function setDocuments(?string $documents): self
    {
        $this->documents = $documents;
        return $this;
    }

    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    public function setSpecialty(?string $specialty): self
    {
        $this->specialty = $specialty;
        return $this;
    }

    public function getExperienceYears(): ?int
    {
        return $this->experienceYears;
    }

    public function setExperienceYears(?int $experienceYears): self
    {
        $this->experienceYears = $experienceYears;
        return $this;
    }

    public function getCertifications(): ?string
    {
        return $this->certifications;
    }

    public function setCertifications(?string $certifications): self
    {
        $this->certifications = $certifications;
        return $this;
    }

    public function getSecurityQuestionId(): ?int
    {
        return $this->securityQuestionId;
    }

    public function setSecurityQuestionId(?int $securityQuestionId): self
    {
        $this->securityQuestionId = $securityQuestionId;
        return $this;
    }

    public function getSecurityAnswer(): ?string
    {
        return $this->securityAnswer;
    }

    public function setSecurityAnswer(?string $securityAnswer): self
    {
        $this->securityAnswer = $securityAnswer;
        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . $this->role];
    }

    public function eraseCredentials(): void
    {
        // Clear temporary sensitive data if any
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }
}