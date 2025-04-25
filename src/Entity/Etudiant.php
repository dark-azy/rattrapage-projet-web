<?php

namespace App\Entity;

use App\Repository\EtudiantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtudiantRepository::class)]
class Etudiant extends User
{
    #[ORM\Column(length: 255)]
    private ?string $nom_etudiant = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom_etudiant = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'En recherche';

    #[ORM\ManyToOne(inversedBy: 'etudiants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Promotion $promotion = null;
    
    #[ORM\ManyToOne(inversedBy: 'etudiants')]
    private ?Adresse $adresse = null;
    
    #[ORM\OneToMany(mappedBy: 'etudiant', targetEntity: Candidature::class)]
    private Collection $candidatures;


    /**
     * @var Collection<int, Pdf>
     */
    #[ORM\OneToMany(targetEntity: Pdf::class, mappedBy: 'etudiant', orphanRemoval: true)]
    private Collection $pdfs;


    public function __construct()
    {
        parent::__construct();
        $this->setRoles(['ROLE_ETUDIANT']);
        $this->candidatures = new ArrayCollection();
        $this->wishlists = new ArrayCollection();
        $this->statut = 'En recherche';
        $this->pdfs = new ArrayCollection();
    }

    public function getNomEtudiant(): ?string
    {
        return $this->nom_etudiant;
    }

    public function setNomEtudiant(string $nom_etudiant): static
    {
        $this->nom_etudiant = $nom_etudiant;

        return $this;
    }

    public function getPrenomEtudiant(): ?string
    {
        return $this->prenom_etudiant;
    }

    public function setPrenomEtudiant(string $prenom_etudiant): static
    {
        $this->prenom_etudiant = $prenom_etudiant;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): static
    {
        $this->promotion = $promotion;

        return $this;
    }
    
    public function getAdresse(): ?Adresse
    {
        return $this->adresse;
    }

    public function setAdresse(?Adresse $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }
    
    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setEtudiant($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getEtudiant() === $this) {
                $candidature->setEtudiant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Wishlist>
     */
    public function getWishlists(): Collection
    {
        return $this->wishlists;
    }

    public function addWishlist(Wishlist $wishlist): static
    {
        if (!$this->wishlists->contains($wishlist)) {
            $this->wishlists->add($wishlist);
            $wishlist->setEtudiant($this);
        }
        return $this;
    }

    public function removeWishlist(Wishlist $wishlist): static
    {
        if ($this->wishlists->removeElement($wishlist)) {
            // set the owning side to null (unless already changed)
            if ($wishlist->getEtudiant() === $this) {
                $wishlist->setEtudiant(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Pdf>
     */
    public function getPdfs(): Collection
    {
        return $this->pdfs;
    }

    public function addPdf(Pdf $pdf): static
    {
        if (!$this->pdfs->contains($pdf)) {
            $this->pdfs->add($pdf);
            $pdf->setEtudiant($this);
        }

        return $this;
    }

    public function removePdf(Pdf $pdf): static
    {
        if ($this->pdfs->removeElement($pdf)) {
            // set the owning side to null (unless already changed)
            if ($pdf->getEtudiant() === $this) {
                $pdf->setEtudiant(null);
            }
        }

        return $this;
    }



}