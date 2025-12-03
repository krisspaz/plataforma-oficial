<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AIRiskScoreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AIRiskScoreRepository::class)]
#[ORM\Table(name: 'ai_risk_scores')]
#[ApiResource(
    normalizationContext: ['groups' => ['airiskscore:read']],
    denormalizationContext: ['groups' => ['airiskscore:write']]
)]
class AIRiskScore
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['airiskscore:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['airiskscore:read', 'airiskscore:write'])]
    private ?Student $student = null;

    #[ORM\Column(length: 20)]
    #[Groups(['airiskscore:read', 'airiskscore:write'])]
    private ?string $riskLevel = null; // low, medium, high, critical

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['airiskscore:read', 'airiskscore:write'])]
    private ?array $factors = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['airiskscore:read', 'airiskscore:write'])]
    private ?array $predictions = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['airiskscore:read'])]
    private ?\DateTimeInterface $calculatedAt = null;

    public function __construct()
    {
        $this->calculatedAt = new \DateTime();
        $this->factors = [];
        $this->predictions = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getRiskLevel(): ?string
    {
        return $this->riskLevel;
    }

    public function setRiskLevel(string $riskLevel): static
    {
        $this->riskLevel = $riskLevel;
        return $this;
    }

    public function getFactors(): ?array
    {
        return $this->factors;
    }

    public function setFactors(?array $factors): static
    {
        $this->factors = $factors;
        return $this;
    }

    public function addFactor(string $key, $value): static
    {
        $this->factors[$key] = $value;
        return $this;
    }

    public function getPredictions(): ?array
    {
        return $this->predictions;
    }

    public function setPredictions(?array $predictions): static
    {
        $this->predictions = $predictions;
        return $this;
    }

    public function addPrediction(string $key, $value): static
    {
        $this->predictions[$key] = $value;
        return $this;
    }

    public function getCalculatedAt(): ?\DateTimeInterface
    {
        return $this->calculatedAt;
    }

    public function setCalculatedAt(\DateTimeInterface $calculatedAt): static
    {
        $this->calculatedAt = $calculatedAt;
        return $this;
    }

    public function isHighRisk(): bool
    {
        return in_array($this->riskLevel, ['high', 'critical']);
    }

    public function getRiskPercentage(): ?float
    {
        return $this->predictions['risk_percentage'] ?? null;
    }
}
