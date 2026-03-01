<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entitat Product — representa un producte publicat al mercat.
 *
 * Cada producte pertany a un usuari (owner) mitjançant una relació ManyToOne.
 * Les dates createdAt i updatedAt s'inicialitzen automàticament al constructor,
 * per garantir que mai siguin null en crear un producte nou.
 *
 * Taula a la base de dades: `product`
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    /**
     * Identificador únic del producte, generat automàticament per la base de dades.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Títol del producte. Màxim 255 caràcters, obligatori.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Descripció detallada del producte. Tipus TEXT (sense límit fix).
     * Opcional — pot ser null.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Preu del producte en euros. Emmagatzemat com a DECIMAL(10,2).
     * Doctrine el retorna com a string per preservar la precisió decimal.
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    /**
     * Data i hora de creació del producte.
     * S'assigna automàticament al constructor — no editable per l'usuari.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Data i hora de l'última modificació.
     * S'assigna al constructor i s'actualitza manualment al controlador en cada edició.
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * URL de la imatge del producte. Màxim 500 caràcters.
     * Opcional — si no s'especifica, el controlador genera una imatge amb Picsum.
     */
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $image = null;

    /**
     * Usuari propietari del producte (costat propietari de la relació).
     *
     * Relació ManyToOne: molts productes poden pertànyer a un mateix usuari.
     * nullable: false — tot producte ha de tenir un owner obligatòriament.
     * La relació inversa (OneToMany) es troba a User::$products.
     */
    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * Inicialitza les dates de creació i modificació automàticament.
     * Això garanteix que mai calgui assignar-les manualment en crear un producte.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ── Getters i Setters ─────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }
}