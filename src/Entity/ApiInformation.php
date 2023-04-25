<?php

namespace App\Entity;

use App\Repository\ApiInformationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiInformationRepository::class)]
class ApiInformation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $baseUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $ApiKey = null;

    #[ORM\OneToMany(mappedBy: 'ApiInformationId', targetEntity: UserApiInformation::class)]
    private Collection $userApiInformation;

    #[ORM\OneToMany(mappedBy: 'targetApi', targetEntity: Target::class)]
    private Collection $targets;

    public function __construct()
    {
        $this->userApiInformation = new ArrayCollection();
        $this->targets = new ArrayCollection();
    }

   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->ApiKey;
    }

    public function setApiKey(string $ApiKey): self
    {
        $this->ApiKey = $ApiKey;

        return $this;
    }

    /**
     * @return Collection<int, UserApiInformation>
     */
    public function getUserApiInformation(): Collection
    {
        return $this->userApiInformation;
    }

    public function addUserApiInformation(UserApiInformation $userApiInformation): self
    {
        if (!$this->userApiInformation->contains($userApiInformation)) {
            $this->userApiInformation->add($userApiInformation);
            $userApiInformation->setApiInformationId($this);
        }

        return $this;
    }

    public function removeUserApiInformation(UserApiInformation $userApiInformation): self
    {
        if ($this->userApiInformation->removeElement($userApiInformation)) {
            // set the owning side to null (unless already changed)
            if ($userApiInformation->getApiInformationId() === $this) {
                $userApiInformation->setApiInformationId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Target>
     */
    public function getTargets(): Collection
    {
        return $this->targets;
    }

    public function addTarget(Target $target): self
    {
        if (!$this->targets->contains($target)) {
            $this->targets->add($target);
            $target->setTargetApi($this);
        }

        return $this;
    }

    public function removeTarget(Target $target): self
    {
        if ($this->targets->removeElement($target)) {
            // set the owning side to null (unless already changed)
            if ($target->getTargetApi() === $this) {
                $target->setTargetApi(null);
            }
        }

        return $this;
    }


    

    
   

}
