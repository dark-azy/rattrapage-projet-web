<?php

namespace App\Entity;

use App\Repository\PiloteDePromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PiloteDePromotionRepository::class)]
class PiloteDePromotion extends User
{
    #[ORM\Column(length: 255)]
    private ?string $nom_pilote = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom_pilote = null;
    
    #[ORM\Column(length: 255)]
    private ?string $email_pilote = null;

    #[ORM\OneToMany(mappedBy: 'pilote', targetEntity: Promotion::class)]
    private Collection $promotions;
    
    #[ORM\OneToMany(mappedBy: 'pilote', targetEntity: OffreDeStage::class)]
    private Collection $offresDeStage;
    
    public function __construct()
    {
        parent::__construct();
        $this->setRoles(['ROLE_PILOTE']);
        $this->promotions = new ArrayCollection();
        $this->offresDeStage = new ArrayCollection();
    }

    public function getNomPilote(): ?string
    {
        return $this->nom_pilote;
    }

    public function setNomPilote(string $nom_pilote): static
    {
        $this->nom_pilote = $nom_pilote;

        return $this;
    }

    public function getPrenomPilote(): ?string
    {
        return $this->prenom_pilote;
    }

    public function setPrenomPilote(string $prenom_pilote): static
    {
        $this->prenom_pilote = $prenom_pilote;

        return $this;
    }
    
    public function getEmailPilote(): ?string
    {
        return $this->email_pilote;
    }

    public function setEmailPilote(string $email_pilote): static
    {
        $this->email_pilote = $email_pilote;

        return $this;
    }
    
    /**
     * @return Collection<int, Promotion>
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): static
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions->add($promotion);
            $promotion->setPilote($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): static
    {
        if ($this->promotions->removeElement($promotion)) {
            // set the owning side to null (unless already changed)
            if ($promotion->getPilote() === $this) {
                $promotion->setPilote(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, OffreDeStage>
     */
    public function getOffresDeStage(): Collection
    {
        return $this->offresDeStage;
    }

    public function addOffreDeStage(OffreDeStage $offreDeStage): static
    {
        if (!$this->offresDeStage->contains($offreDeStage)) {
            $this->offresDeStage->add($offreDeStage);
            $offreDeStage->setPilote($this);
        }

        return $this;
    }

    public function removeOffreDeStage(OffreDeStage $offreDeStage): static
    {
        if ($this->offresDeStage->removeElement($offreDeStage)) {
            // set the owning side to null (unless already changed)
            if ($offreDeStage->getPilote() === $this) {
                $offreDeStage->setPilote(null);
            }
        }

        return $this;
    }
}