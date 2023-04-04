<?php

namespace App\Entity;

use App\Repository\UserApiInformationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserApiInformationRepository::class)]
class UserApiInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userApiInformation')]
    private ?User $UserId = null;

    #[ORM\ManyToOne(inversedBy: 'userApiInformation')]
    private ?ApiInformation $ApiInformationId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->UserId;
    }

    public function setUserId(?User $UserId): self
    {
        $this->UserId = $UserId;

        return $this;
    }

    public function getApiInformationId(): ?ApiInformation
    {
        return $this->ApiInformationId;
    }

    public function setApiInformationId(?ApiInformation $ApiInformationId): self
    {
        $this->ApiInformationId = $ApiInformationId;

        return $this;
    }
}
