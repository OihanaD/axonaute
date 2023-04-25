<?php

namespace App\Entity;

use App\Repository\TargetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TargetRepository::class)]
class Target
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $value = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\Column(length: 255)]
    private ?string $kpi = null;

    #[ORM\ManyToOne(inversedBy: 'targets')]
    private ?ApiInformation $targetApi = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getKpi(): ?string
    {
        return $this->kpi;
    }

    public function setKpi(string $kpi): self
    {
        $this->kpi = $kpi;

        return $this;
    }

    public function getTargetApi(): ?ApiInformation
    {
        return $this->targetApi;
    }

    public function setTargetApi(?ApiInformation $targetApi): self
    {
        $this->targetApi = $targetApi;

        return $this;
    }
}
