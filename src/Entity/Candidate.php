<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Assert\Callback(['App\Entity\Candidate', 'validate'])]
class Candidate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email "{{ value }}" n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column]
    private ?bool $hasExperience = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $experienceDetails = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $availabilityDate = null;

    #[ORM\Column]
    private ?bool $isImmediatelyAvailable = false;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['draft', 'submitted'], message: 'Le statut doit être "draft" ou "submitted".')]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Assert\IsTrue(message: 'Vous devez accepter le consentement RGPD pour soumettre votre candidature.')]
    private ?bool $consentRGPD = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function isHasExperience(): ?bool
    {
        return $this->hasExperience;
    }

    public function setHasExperience(bool $hasExperience): static
    {
        $this->hasExperience = $hasExperience;

        return $this;
    }

    public function getExperienceDetails(): ?string
    {
        return $this->experienceDetails;
    }

    public function setExperienceDetails(?string $experienceDetails): static
    {
        $this->experienceDetails = $experienceDetails;

        return $this;
    }

    public function getAvailabilityDate(): ?\DateTimeInterface
    {
        return $this->availabilityDate;
    }

    public function setAvailabilityDate(\DateTimeInterface $availabilityDate = null): static
    {
        $this->availabilityDate = $availabilityDate;

        return $this;
    }

    public function isIsImmediatelyAvailable(): ?bool
    {
        return $this->isImmediatelyAvailable;
    }

    public function setIsImmediatelyAvailable(bool $isImmediatelyAvailable): static
    {
        $this->isImmediatelyAvailable = $isImmediatelyAvailable;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isConsentRGPD(): ?bool
    {
        return $this->consentRGPD;
    }

    public function setConsentRGPD(bool $consentRGPD): static
    {
        $this->consentRGPD = $consentRGPD;

        return $this;
    }

    public static function validate(object $object, ExecutionContextInterface $context): void
    {
        if ($object->isHasExperience() && empty($object->getExperienceDetails())) {
            $context->buildViolation('Veuillez détailler votre expérience professionnelle.')
                ->atPath('experienceDetails')
                ->addViolation();
        }

        if (!$object->isIsImmediatelyAvailable() && empty($object->getAvailabilityDate())) {
            $context->buildViolation('Veuillez indiquer une date de disponibilité.')
                ->atPath('availabilityDate')
                ->addViolation();
        }
    }
}