<?php

namespace App\Entity;

use App\Repository\OffreDeStageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OffreDeStageRepository::class)]
#[ORM\Table(name: "offre_de_stage")]
class OffreDeStage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id_offre")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre_offre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description_offre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $competences_requises = null;

    #[ORM\Column]
    private ?int $duree_stage = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_debut_stage = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_fin_stage = null;

    #[ORM\Column]
    private ?float $salaire = null;

    #[ORM\Column(length: 50)]
    private ?string $statut_offre = null;

    #[ORM\ManyToOne(inversedBy: 'offresDeStage')]
    #[ORM\JoinColumn(name: "id_pilote", referencedColumnName: "id", nullable: false)]
    private ?PiloteDePromotion $pilote = null;

    #[ORM\ManyToOne(inversedBy: 'offresDeStage')]
    #[ORM\JoinColumn(name: "id_entreprise", referencedColumnName: "id", nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\OneToMany(mappedBy: 'offreDeStage', targetEntity: Candidature::class)]
    private Collection $candidatures;

    #[ORM\OneToMany(mappedBy: 'offreDeStage', targetEntity: Wishlist::class, orphanRemoval: true)]
    private Collection $wishlists;

    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
        $this->wishlists = new ArrayCollection();
        $this->statut_offre = 'Disponible';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreOffre(): ?string
    {
        return $this->titre_offre;
    }

    public function setTitreOffre(string $titre_offre): static
    {
        $this->titre_offre = $titre_offre;

        return $this;
    }

    public function getDescriptionOffre(): ?string
    {
        return $this->description_offre;
    }

    public function setDescriptionOffre(string $description_offre): static
    {
        $this->description_offre = $description_offre;

        return $this;
    }

    public function getCompetencesRequises(): ?string
    {
        return $this->competences_requises;
    }

    public function setCompetencesRequises(string $competences_requises): static
    {
        $this->competences_requises = $competences_requises;
        return $this;
    }

    public function getDureeStage(): ?int
    {
        return $this->duree_stage;
    }

    public function setDureeStage(int $duree_stage): static
    {
        $this->duree_stage = $duree_stage;

        return $this;
    }

    public function getDateDebutStage(): ?\DateTimeInterface
    {
        return $this->date_debut_stage;
    }

    public function setDateDebutStage(\DateTimeInterface $date_debut_stage): static
    {
        $this->date_debut_stage = $date_debut_stage;

        return $this;
    }

    public function getDateFinStage(): ?\DateTimeInterface
    {
        return $this->date_fin_stage;
    }

    public function setDateFinStage(\DateTimeInterface $date_fin_stage): static
    {
        $this->date_fin_stage = $date_fin_stage;

        return $this;
    }

    public function getSalaire(): ?float
    {
        return $this->salaire;
    }

    public function setSalaire(float $salaire): static
    {
        $this->salaire = $salaire;

        return $this;
    }

    public function getStatutOffre(): ?string
    {
        return $this->statut_offre;
    }

    public function setStatutOffre(string $statut_offre): static
    {
        $this->statut_offre = $statut_offre;

        return $this;
    }

    public function getPilote(): ?PiloteDePromotion
    {
        return $this->pilote;
    }

    public function setPilote(?PiloteDePromotion $pilote): static
    {
        $this->pilote = $pilote;

        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

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
            $candidature->setOffreDeStage($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getOffreDeStage() === $this) {
                $candidature->setOffreDeStage(null);
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
            $wishlist->setOffreDeStage($this);
        }

        return $this;
    }

    public function removeWishlist(Wishlist $wishlist): static
    {
        if ($this->wishlists->removeElement($wishlist)) {
            // set the owning side to null (unless already changed)
            if ($wishlist->getOffreDeStage() === $this) {
                $wishlist->setOffreDeStage(null);
            }
        }

        return $this;
    }
}
