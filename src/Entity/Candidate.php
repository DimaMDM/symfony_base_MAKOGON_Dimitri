<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Candidate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidate(): ?string
    {
        return $this->Candidate;
    }

    public function setCandidate(?string $Candidate): static
    {
        $this->Candidate = $Candidate;

        return $this;
    }
}
