<?php

namespace App\Entity;

use App\Repository\AdministrateurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdministrateurRepository::class)]
class Administrateur extends User
{
    #[ORM\Column(length: 255)]
    private ?string $nom_admin = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom_admin = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->setRoles(['ROLE_ADMIN']);
    }

    public function getNomAdmin(): ?string
    {
        return $this->nom_admin;
    }

    public function setNomAdmin(string $nom_admin): static
    {
        $this->nom_admin = $nom_admin;

        return $this;
    }

    public function getPrenomAdmin(): ?string
    {
        return $this->prenom_admin;
    }

    public function setPrenomAdmin(string $prenom_admin): static
    {
        $this->prenom_admin = $prenom_admin;

        return $this;
    }
}
