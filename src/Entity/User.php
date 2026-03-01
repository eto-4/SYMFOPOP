<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Entitat User — representa un usuari registrat a l'aplicació.
 *
 * Implementa dues interfícies de Symfony Security:
 * - UserInterface: proporciona el mètode getUserIdentifier() (l'email) i getRoles()
 * - PasswordAuthenticatedUserInterface: indica que l'usuari s'autentica amb contrasenya
 *
 * La restricció UniqueEntity garanteix que no hi pugui haver dos comptes amb el mateix email,
 * tant a nivell de base de dades (UniqueConstraint) com a nivell de validació de formulari.
 *
 * Taula a la base de dades: `user`
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Identificador únic de l'usuari, generat automàticament per la base de dades.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Correu electrònic de l'usuari. Màxim 180 caràcters.
     * Actua com a identificador principal per al login (getUserIdentifier).
     * Ha de ser únic a tota la taula.
     */
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * Nom visible de l'usuari. Màxim 255 caràcters.
     * Es mostra a la navbar i a les targetes de producte.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Llista de rols de l'usuari emmagatzemada com a JSON.
     * Exemple: ["ROLE_ADMIN"] o [] (buit = només ROLE_USER per defecte).
     * getRoles() sempre afegeix ROLE_USER automàticament.
     *
     * @var list<string>
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * Contrasenya hashejada de l'usuari.
     * Mai es desa en text pla — sempre s'usa UserPasswordHasher per hashejar.
     *
     * @var string
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * Col·lecció de productes publicats per aquest usuari.
     *
     * Relació OneToMany: un usuari pot tenir molts productes.
     * orphanRemoval: true — si s'esborra l'usuari, s'esborren també els seus productes.
     * La relació propietària (ManyToOne) es troba a Product::$owner.
     *
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $products;

    /**
     * Inicialitza la col·lecció de productes com a ArrayCollection buida.
     * Necessari per evitar errors en accedir a $this->products abans de persistir l'entitat.
     */
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    // ── Getters i Setters ─────────────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
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

    /**
     * Retorna l'identificador visual de l'usuari per al sistema d'autenticació.
     * Symfony utilitza aquest valor per identificar l'usuari a la sessió.
     * En aquesta aplicació l'identificador és l'email.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Retorna la llista de rols de l'usuari.
     * ROLE_USER s'afegeix sempre automàticament, fins i tot si $roles és buit.
     * array_unique evita duplicats en cas que ROLE_USER ja estigui a $roles.
     *
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Tot usuari té com a mínim ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Retorna la contrasenya hashejada de l'usuari.
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Serialitza l'usuari per emmagatzemar-lo a la sessió.
     *
     * Per seguretat, substitueix el hash de la contrasenya per un hash CRC32C
     * abans de serialitzar. Això evita que el hash real de la contrasenya
     * quedi exposat a la sessió, però permet detectar si la contrasenya
     * ha canviat (invalidant la sessió activa).
     * Suportat des de Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    /**
     * Esborra credencials sensibles de la memòria després de l'autenticació.
     * Deprecat a Symfony 7 — es manté per compatibilitat fins a Symfony 8.
     *
     * @deprecated S'eliminarà en actualitzar a Symfony 8
     */
    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, s'eliminarà en actualitzar a Symfony 8
    }

    // ── Gestió de la col·lecció de productes ──────────────────────────────────

    /**
     * Retorna tots els productes publicats per aquest usuari.
     *
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * Afegeix un producte a la col·lecció i assigna aquest usuari com a owner.
     * Comprova si ja existeix a la col·lecció per evitar duplicats.
     */
    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            // Sincronitzem el costat propietari de la relació
            $product->setOwner($this);
        }
        return $this;
    }

    /**
     * Elimina un producte de la col·lecció.
     * Si el producte tenia aquest usuari com a owner, el posa a null
     * per mantenir la coherència de la relació bidireccional.
     */
    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // Netegem el costat propietari només si encara apunta a aquest usuari
            if ($product->getOwner() === $this) {
                $product->setOwner(null);
            }
        }
        return $this;
    }
}